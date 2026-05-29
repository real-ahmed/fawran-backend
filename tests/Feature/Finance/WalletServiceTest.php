<?php

namespace Tests\Feature\Finance;

use App\Enums\WalletTransactionType;
use App\Models\Payment\Settlement;
use App\Models\Payment\Wallet;
use App\Models\User;
use App\Services\Finance\WalletService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use LazilyRefreshDatabase;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->walletService = app(WalletService::class);
    }

    public function test_deposit_increases_balance_and_creates_transaction(): void
    {
        $user = User::factory()->create();

        // Create a reference model for the transaction
        $reference = Settlement::create([
            'settlement_type' => 'store',
            'target_id' => 1,
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'total_gross' => 100,
            'total_deductions' => 0,
            'total_net_exchange' => 100,
            'status' => 'pending',
        ]);

        $transaction = $this->walletService->deposit(
            $user,
            100.50,
            WalletTransactionType::Deposit,
            $reference,
        );

        $this->assertNotNull($transaction);
        $this->assertEquals('100.50', $transaction->amount);
        $this->assertEquals(WalletTransactionType::Deposit, $transaction->type);

        $balance = $this->walletService->getBalance($user);
        $this->assertEquals(100.50, $balance);
    }

    public function test_withdraw_decreases_balance_and_creates_transaction(): void
    {
        $user = User::factory()->create();

        $reference = Settlement::create([
            'settlement_type' => 'store',
            'target_id' => 1,
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'total_gross' => 200,
            'total_deductions' => 0,
            'total_net_exchange' => 200,
            'status' => 'pending',
        ]);

        // Deposit first
        $this->walletService->deposit($user, 200.00, WalletTransactionType::Deposit, $reference);

        // Withdraw
        $transaction = $this->walletService->withdraw(
            $user,
            75.00,
            WalletTransactionType::Withdrawal,
            $reference,
        );

        $this->assertNotNull($transaction);
        $this->assertEquals('75.00', $transaction->amount);

        $balance = $this->walletService->getBalance($user);
        $this->assertEquals(125.00, $balance);
    }

    public function test_withdraw_insufficient_funds_throws_exception(): void
    {
        $user = User::factory()->create();

        $reference = Settlement::create([
            'settlement_type' => 'store',
            'target_id' => 1,
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'total_gross' => 50,
            'total_deductions' => 0,
            'total_net_exchange' => 50,
            'status' => 'pending',
        ]);

        // Deposit only 50
        $this->walletService->deposit($user, 50.00, WalletTransactionType::Deposit, $reference);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient wallet balance');

        $this->walletService->withdraw($user, 100.00, WalletTransactionType::Withdrawal, $reference);
    }

    public function test_deposit_with_zero_amount_throws_exception(): void
    {
        $user = User::factory()->create();

        $reference = Settlement::create([
            'settlement_type' => 'store',
            'target_id' => 1,
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net_exchange' => 0,
            'status' => 'pending',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Deposit amount must be positive');

        $this->walletService->deposit($user, 0, WalletTransactionType::Deposit, $reference);
    }

    public function test_wallet_auto_created_on_first_operation(): void
    {
        $user = User::factory()->create();

        // User should not have a wallet yet
        $this->assertNull(Wallet::where('user_id', $user->id)->first());

        $wallet = $this->walletService->getOrCreateWallet($user);

        $this->assertNotNull($wallet);
        $this->assertEquals($user->id, $wallet->user_id);
        $this->assertEquals('0.00', $wallet->balance);
    }

    public function test_withdraw_available_deducts_partial_amount(): void
    {
        $user = User::factory()->create();

        $reference = Settlement::create([
            'settlement_type' => 'courier',
            'target_id' => 1,
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'total_gross' => 100,
            'total_deductions' => 0,
            'total_net_exchange' => 100,
            'status' => 'pending',
        ]);

        // Deposit 30
        $this->walletService->deposit($user, 30.00, WalletTransactionType::Deposit, $reference);

        // Try to withdraw 100, should only get 30
        $deducted = $this->walletService->withdrawAvailable(
            $user,
            100.00,
            WalletTransactionType::CodDeduction,
            $reference,
        );

        $this->assertEquals(30.00, $deducted);
        $this->assertEquals(0.00, $this->walletService->getBalance($user));
    }

    public function test_multiple_deposits_accumulate(): void
    {
        $user = User::factory()->create();

        $reference = Settlement::create([
            'settlement_type' => 'store',
            'target_id' => 1,
            'period_start' => now()->subDays(7),
            'period_end' => now(),
            'total_gross' => 300,
            'total_deductions' => 0,
            'total_net_exchange' => 300,
            'status' => 'pending',
        ]);

        $this->walletService->deposit($user, 100.00, WalletTransactionType::Deposit, $reference);
        $this->walletService->deposit($user, 50.00, WalletTransactionType::DeliveryEarning, $reference);
        $this->walletService->deposit($user, 25.50, WalletTransactionType::CommissionEarning, $reference);

        $this->assertEquals(175.50, $this->walletService->getBalance($user));

        // Verify all transactions were created
        $wallet = $this->walletService->getOrCreateWallet($user);
        $this->assertCount(3, $wallet->transactions);
    }
}
