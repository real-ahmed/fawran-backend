<?php

namespace Tests\Feature\Finance;

use App\Enums\PayoutRequestStatus;
use App\Models\Admin;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\Wallet;
use App\Models\Platform\SystemSetting;
use App\Models\User;
use App\Services\Admin\PayoutService;
use App\Services\Finance\WalletService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PayoutFlowTest extends TestCase
{
    use LazilyRefreshDatabase;

    private PayoutService $payoutService;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payoutService = app(PayoutService::class);
        $this->walletService = app(WalletService::class);
        SystemSetting::updateOrCreate(['key' => 'payout_minimum_threshold'], ['value' => '500.00', 'group' => 'financial']);
    }

    public function test_approve_payout_deducts_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 1000.00]);

        $payout = PayoutRequest::create(['user_id' => $user->id, 'amount' => 600.00, 'bank_details' => 'IBAN: EG123', 'status' => PayoutRequestStatus::Pending]);

        $admin = Admin::factory()->create();
        $result = $this->payoutService->approvePayoutRequest($payout, $admin);

        $this->assertEquals(PayoutRequestStatus::Transferred, $result->status);
        $this->assertEquals(400.00, (float) $wallet->fresh()->balance);
    }

    public function test_reject_payout_preserves_balance(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'balance' => 1000.00]);

        $payout = PayoutRequest::create(['user_id' => $user->id, 'amount' => 600.00, 'bank_details' => 'IBAN: EG123', 'status' => PayoutRequestStatus::Pending]);

        $this->payoutService->rejectPayoutRequest($payout);

        $this->assertEquals(1000.00, (float) $wallet->fresh()->balance);
        $this->assertEquals(PayoutRequestStatus::Rejected, $payout->fresh()->status);
    }

    public function test_minimum_threshold_enforced(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance' => 1000.00]);

        $payout = PayoutRequest::create(['user_id' => $user->id, 'amount' => 100.00, 'bank_details' => 'IBAN: EG123', 'status' => PayoutRequestStatus::Pending]);

        $admin = Admin::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('below the minimum threshold');

        $this->payoutService->approvePayoutRequest($payout, $admin);
    }

    public function test_insufficient_balance_blocked(): void
    {
        $user = User::factory()->create();
        Wallet::create(['user_id' => $user->id, 'balance' => 100.00]);

        $payout = PayoutRequest::create(['user_id' => $user->id, 'amount' => 600.00, 'bank_details' => 'IBAN: EG123', 'status' => PayoutRequestStatus::Pending]);

        $admin = Admin::factory()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        $this->payoutService->approvePayoutRequest($payout, $admin);
    }
}
