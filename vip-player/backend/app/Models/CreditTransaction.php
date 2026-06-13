<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_PURCHASE = 'purchase';

    public const TYPES = [
        self::TYPE_CREDIT,
        self::TYPE_DEBIT,
        self::TYPE_REFUND,
        self::TYPE_ADJUSTMENT,
        self::TYPE_PURCHASE,
    ];

    protected $fillable = [
        'wallet_id',
        'reseller_id',
        'type',
        'amount',
        'balance_after',
        'description',
        'subscription_id',
        'admin_id',
        'stripe_payment_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after' => 'integer',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
