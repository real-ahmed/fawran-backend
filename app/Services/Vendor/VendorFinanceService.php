<?php

namespace App\Services\Vendor;

use App\Enums\PayoutRequestStatus;
use App\Models\Payment\PayoutRequest;
use App\Models\Payment\Wallet;
use App\Models\Vendor\Vendor;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class VendorFinanceService
{
    public function getWallet(Vendor $vendor): Wallet
    {
        $wallet = $this->walletForVendor($vendor);

        return $wallet->load(['transactions' => function ($query) {
            $query->latest('id')->limit(50);
        }]);
    }

    public function listPayoutRequests(Vendor $vendor): LengthAwarePaginator
    {
        return PayoutRequest::where('user_id', $vendor->owner_id)
            ->latest('id')
            ->paginate(20);
    }

    public function createPayoutRequest(Vendor $vendor, array $data): PayoutRequest
    {
        return DB::transaction(function () use ($vendor, $data) {
            $wallet = $this->walletForVendor($vendor);
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            if ($wallet->balance < $data['amount']) {
                throw new HttpException(400, __('messages.insufficient_funds'));
            }

            $wallet->balance -= $data['amount'];
            $wallet->save();

            return PayoutRequest::create([
                'user_id' => $vendor->owner_id,
                'amount' => $data['amount'],
                'bank_details' => $data['bank_details'],
                'status' => PayoutRequestStatus::Pending->value,
            ]);
        });
    }

    private function walletForVendor(Vendor $vendor): Wallet
    {
        $vendor->loadMissing('owner.wallet');

        if ($vendor->owner?->wallet) {
            return $vendor->owner->wallet;
        }

        return Wallet::create([
            'user_id' => $vendor->owner_id,
            'balance' => 0.00,
        ]);
    }
}
