<?php

namespace App\Services\Finance;

use App\Enums\WalletTransactionType;
use App\Models\Payment\Wallet;
use App\Models\Payment\WalletTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class WalletService
{
    /**
     * Deposit funds into a user's wallet. Always creates a transaction record first.
     *
     * @param  array{description?: string}  $meta
     */
    public function deposit(
        User $user,
        float $amount,
        WalletTransactionType $type,
        Model $reference,
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new RuntimeException('Deposit amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $reference) {
            $wallet = $this->getOrCreateWallet($user);

            /** @var Wallet $wallet */
            $wallet = Wallet::query()->lockForUpdate()->find($wallet->id);

            $transaction = $wallet->transactions()->create([
                'amount' => $amount,
                'type' => $type,
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
            ]);

            $wallet->increment('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Withdraw funds from a user's wallet with insufficient-funds guard.
     */
    public function withdraw(
        User $user,
        float $amount,
        WalletTransactionType $type,
        Model $reference,
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new RuntimeException('Withdrawal amount must be positive.');
        }

        return DB::transaction(function () use ($user, $amount, $type, $reference) {
            $wallet = $this->getOrCreateWallet($user);

            /** @var Wallet $wallet */
            $wallet = Wallet::query()->lockForUpdate()->find($wallet->id);

            if ((float) $wallet->balance < $amount) {
                throw new RuntimeException(
                    "Insufficient wallet balance. Available: {$wallet->balance}, Requested: {$amount}"
                );
            }

            $transaction = $wallet->transactions()->create([
                'amount' => $amount,
                'type' => $type,
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
            ]);

            $wallet->decrement('balance', $amount);

            return $transaction;
        });
    }

    /**
     * Attempt a partial withdrawal — deducts whatever is available up to $amount.
     * Returns the actual amount deducted (may be 0 if wallet is empty).
     */
    public function withdrawAvailable(
        User $user,
        float $amount,
        WalletTransactionType $type,
        Model $reference,
    ): float {
        if ($amount <= 0) {
            return 0.0;
        }

        return DB::transaction(function () use ($user, $amount, $type, $reference): float {
            $wallet = $this->getOrCreateWallet($user);

            /** @var Wallet $wallet */
            $wallet = Wallet::query()->lockForUpdate()->find($wallet->id);

            $available = min((float) $wallet->balance, $amount);

            if ($available <= 0) {
                return 0.0;
            }

            $wallet->transactions()->create([
                'amount' => $available,
                'type' => $type,
                'reference_type' => $reference->getMorphClass(),
                'reference_id' => $reference->getKey(),
            ]);

            $wallet->decrement('balance', $available);

            return $available;
        });
    }

    /**
     * Get the current wallet balance for a user.
     */
    public function getBalance(User $user): float
    {
        return (float) $this->getOrCreateWallet($user)->balance;
    }

    /**
     * Ensure a wallet exists for the given user (lazy creation).
     */
    public function getOrCreateWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );
    }
}
