<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'plan_type',
        'duration_days',
        'credit_cost',
        'price_usd',
        'is_trial',
        'is_lifetime',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
            'credit_cost' => 'integer',
            'price_usd' => 'decimal:2',
            'is_trial' => 'boolean',
            'is_lifetime' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
