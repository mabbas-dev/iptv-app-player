<?php

namespace App\Services;

use App\Models\CreditTransaction;
use App\Models\Reseller;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Every balance change goes through this service so a ledger entry
 * (credit_transactions) is always written alongside the wallet update.
 */
class CreditService
{
    /**
     * Add credits to a reseller wallet.
     */
    public function credit(
        Reseller $reseller,
        int $amount,
        string $type = CreditTransaction::TYPE_CREDIT,
        ?string $description = null,
        ?int $adminId = null,
        ?string $stripePaymentId = null,
    ): CreditTransaction {
        if ($amount <= 0) {
            throw new RuntimeException('Credit amount must be positive.');
        }

        return DB::transaction(function () use ($reseller, $amount, $type, $description, $adminId, $stripePaymentId) {
            $wallet = $reseller->wallet()->lockForUpdate()->first();
            $wallet->increment('balance', $amount);

            return CreditTransaction::create([
                'wallet_id' => $wallet->id,
                'reseller_id' => $reseller->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $wallet->fresh()->balance,
                'description' => $description,
                'admin_id' => $adminId,
                'stripe_payment_id' => $stripePaymentId,
            ]);
        });
    }

    /**
     * Remove credits from a reseller wallet. Fails if balance is insufficient.
     */
    public function debit(
        Reseller $reseller,
        int $amount,
        string $type = CreditTransaction::TYPE_DEBIT,
        ?string $description = null,
        ?int $subscriptionId = null,
        ?int $adminId = null,
    ): CreditTransaction {
        if ($amount <= 0) {
            throw new RuntimeException('Debit amount must be positive.');
        }

        return DB::transaction(function () use ($reseller, $amount, $type, $description, $subscriptionId, $adminId) {
            $wallet = $reseller->wallet()->lockForUpdate()->first();

            if ($wallet->balance < $amount) {
                throw new RuntimeException('Insufficient credit balance.');
            }

            $wallet->decrement('balance', $amount);

            return CreditTransaction::create([
                'wallet_id' => $wallet->id,
                'reseller_id' => $reseller->id,
                'type' => $type,
                'amount' => -$amount,
                'balance_after' => $wallet->fresh()->balance,
                'description' => $description,
                'subscription_id' => $subscriptionId,
                'admin_id' => $adminId,
            ]);
        });
    }
}
