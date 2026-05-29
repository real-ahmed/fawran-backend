<?php

namespace App\Services\Admin;

use App\DTOs\Admin\PayoutRequest\PayoutRequestFilterDTO;
use App\Enums\PayoutRequestStatus;
use App\Enums\WalletTransactionType;
use App\Models\Admin;
use App\Models\Payment\PayoutRequest;
use App\Models\Platform\SystemSetting;
use App\Services\Finance\PlatformWalletService;
use App\Services\Finance\WalletService;
use App\Traits\Paginatable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PayoutService
{
    use Paginatable;

    public function __construct(
        private WalletService $walletService,
        private PlatformWalletService $platformWalletService,
    ) {}

    public function listPayoutRequests(PayoutRequestFilterDTO $filters)
    {
        return PayoutRequest::query()
            ->withListRelations()
            ->forAdminZones()
            ->status($filters->status)
            ->newest()
            ->cursorPaginate($this->getPerPageLimit());
    }

    public function approvePayoutRequest(PayoutRequest $payoutRequest, ?Admin $admin = null): PayoutRequest
    {
        if ($payoutRequest->status !== PayoutRequestStatus::Pending) {
            throw new RuntimeException('Only pending payout requests can be approved.');
        }

        $admin ??= auth('api_admin')->user();
        $amount = (float) $payoutRequest->amount;

        // Validate minimum threshold
        $minThreshold = (float) SystemSetting::cachedValue('payout_minimum_threshold', '0');
        if ($minThreshold > 0 && $amount < $minThreshold) {
            throw new RuntimeException(
                "Payout amount ({$amount}) is below the minimum threshold ({$minThreshold})."
            );
        }

        // Validate wallet balance
        $balance = $this->walletService->getBalance($payoutRequest->user);
        if ($balance < $amount) {
            throw new RuntimeException(
                "Insufficient wallet balance. Available: {$balance}, Requested: {$amount}"
            );
        }

        return DB::transaction(function () use ($payoutRequest, $admin, $amount) {
            // Deduct from user wallet
            $this->walletService->withdraw(
                $payoutRequest->user,
                $amount,
                WalletTransactionType::PayoutWithdrawal,
                $payoutRequest,
            );

            // Debit platform wallet
            $this->platformWalletService->debit($amount);

            // Record execution
            $payoutRequest->execution()->create([
                'admin_id' => $admin?->id,
                'executed_at' => now(),
            ]);

            $payoutRequest->update(['status' => PayoutRequestStatus::Transferred]);

            return $payoutRequest->load('execution');
        });
    }

    public function rejectPayoutRequest(PayoutRequest $payoutRequest): PayoutRequest
    {
        if ($payoutRequest->status !== PayoutRequestStatus::Pending) {
            throw new RuntimeException('Only pending payout requests can be rejected.');
        }

        $payoutRequest->update(['status' => PayoutRequestStatus::Rejected]);

        return $payoutRequest;
    }
}
