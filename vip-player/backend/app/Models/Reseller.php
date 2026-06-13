<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Reseller extends Authenticatable implements FilamentUser
{
    use Notifiable;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SUSPENDED = 'suspended';

    public const PERM_ACTIVATE = 'activate_device';
    public const PERM_VIEW_DEVICES = 'view_devices';
    public const PERM_API = 'use_api';

    public const ALL_PERMISSIONS = [
        self::PERM_ACTIVATE => 'Activate devices (MAC)',
        self::PERM_VIEW_DEVICES => 'View devices',
        self::PERM_API => 'Use API keys',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'company_name',
        'store_name',
        'store_slug',
        'store_image',
        'store_description',
        'store_url',
        'store_whatsapp',
        'store_email',
        'show_in_directory',
        'status',
        'stripe_customer_id',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'show_in_directory' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Reseller $reseller) {
            $reseller->wallet()->create(['balance' => 0]);
            if (empty($reseller->permissions)) {
                $reseller->update(['permissions' => [self::PERM_ACTIVATE]]);
            }
        });
    }

    public function hasPermission(string $permission): bool
    {
        $perms = $this->permissions ?? [self::PERM_ACTIVATE];

        return in_array($permission, $perms, true);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'reseller' && $this->status === self::STATUS_ACTIVE;
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getBalanceAttribute(): int
    {
        return $this->wallet?->balance ?? 0;
    }
}
