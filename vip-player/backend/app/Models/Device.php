<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_TRIAL,
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_BLOCKED,
        self::STATUS_SUSPENDED,
    ];

    protected $fillable = [
        'device_code',
        'device_uuid',
        'platform',
        'app_version',
        'reseller_id',
        'user_id',
        'status',
        'trial_started_at',
        'trial_ends_at',
        'subscription_ends_at',
        'parental_pin_hash',
        'parental_lock_enabled',
        'mac_locked',
        'last_seen_at',
        'playlist_synced_at',
        'is_lifetime',
    ];

    protected $hidden = [
        'parental_pin_hash',
    ];

    protected function casts(): array
    {
        return [
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'subscription_ends_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'parental_lock_enabled' => 'boolean',
            'mac_locked' => 'boolean',
            'is_lifetime' => 'boolean',
            'playlist_synced_at' => 'datetime',
        ];
    }

    /**
     * Generate a unique MAC-style device code, e.g. A1:B2:C3:D4:E5:F6.
     * Never uses the real hardware MAC address.
     */
    public static function generateDeviceCode(): string
    {
        do {
            $bytes = random_bytes(6);
            $code = strtoupper(implode(':', str_split(bin2hex($bytes), 2)));
        } while (static::where('device_code', $code)->exists());

        return $code;
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function playlist(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Playlist::class);
    }

    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class, 'device_playlists')
            ->withPivot(['is_default', 'sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Latest expiry across trial and paid subscription (whichever is further).
     */
    public function effectiveExpiry(): ?\Illuminate\Support\Carbon
    {
        if ($this->is_lifetime) {
            return null;
        }

        $dates = array_filter([
            $this->subscription_ends_at,
            $this->trial_ends_at,
        ]);

        return empty($dates) ? null : collect($dates)->max();
    }

    /**
     * Date from which new plan duration should be stacked.
     */
    public function renewalBase(): \Illuminate\Support\Carbon
    {
        $expiry = $this->effectiveExpiry();

        return ($expiry && $expiry->isFuture()) ? $expiry->copy() : now();
    }

    public function hasDirectCustomerPurchase(): bool
    {
        return $this->subscriptions()
            ->whereNotNull('stripe_payment_id')
            ->whereNull('reseller_id')
            ->exists();
    }

    /**
     * Refresh status based on expiry dates. Returns the effective status.
     */
    public function refreshStatus(): string
    {
        if (in_array($this->status, [self::STATUS_BLOCKED, self::STATUS_SUSPENDED], true)) {
            return $this->status;
        }

        if ($this->is_lifetime && $this->status === self::STATUS_ACTIVE) {
            return $this->status;
        }

        $expiry = $this->effectiveExpiry();

        if ($expiry?->isPast() && in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE], true)) {
            $this->update(['status' => self::STATUS_EXPIRED]);
        }

        return $this->status;
    }

    public function isWatchable(): bool
    {
        $status = $this->refreshStatus();

        if ($this->is_lifetime && $status === self::STATUS_ACTIVE) {
            return true;
        }

        return in_array($status, [self::STATUS_TRIAL, self::STATUS_ACTIVE], true);
    }

    public function expiresAt(): ?\Illuminate\Support\Carbon
    {
        if ($this->is_lifetime) {
            return null;
        }

        return $this->effectiveExpiry();
    }
}
