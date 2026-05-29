<?php

namespace App\Services\Finance;

use App\Models\Platform\PlatformWallet;
use Illuminate\Support\Facades\DB;

class PlatformWalletService
{
    /**
     * Credit the platform wallet (revenue received).
     * Updates both current_balance and total_revenue.
     */
    public function credit(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($amount) {
            $wallet = $this->getWallet(forUpdate: true);

            $wallet->increment('current_balance', $amount);
            $wallet->increment('total_revenue', $amount);
        });
    }

    /**
     * Debit the platform wallet (payout to vendor/courier).
     * Only decreases current_balance, not total_revenue.
     */
    public function debit(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        DB::transaction(function () use ($amount) {
            $wallet = $this->getWallet(forUpdate: true);

            $wallet->decrement('current_balance', $amount);
        });
    }

    /**
     * Get the platform wallet balance summary.
     *
     * @return array{total_revenue: string, current_balance: string}
     */
    public function getBalance(): array
    {
        $wallet = $this->getWallet();

        return [
            'total_revenue' => $wallet->total_revenue,
            'current_balance' => $wallet->current_balance,
        ];
    }

    /**
     * Get or create the singleton platform wallet.
     */
    private function getWallet(bool $forUpdate = false): PlatformWallet
    {
        $query = PlatformWallet::query();

        if ($forUpdate) {
            $query->lockForUpdate();
        }

        return $query->firstOrCreate([], [
            'total_revenue' => 0,
            'current_balance' => 0,
        ]);
    }
}
