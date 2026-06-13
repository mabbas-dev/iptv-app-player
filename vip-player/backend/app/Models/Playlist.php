<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Playlist extends Model
{
    public const TYPE_XTREAM = 'xtream';
    public const TYPE_M3U = 'm3u';
    public const TYPE_M3U8 = 'm3u8';
    public const TYPE_DIRECT = 'direct';

    public const TYPES = [
        self::TYPE_XTREAM,
        self::TYPE_M3U,
        self::TYPE_M3U8,
        self::TYPE_DIRECT,
    ];

    protected $fillable = [
        'name',
        'type',
        'server_url',
        'username',
        'password',
        'url',
        'file_path',
        'epg_url',
        'reseller_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class);
    }

    public function devices(): BelongsToMany
    {
        return $this->belongsToMany(Device::class, 'device_playlists')
            ->withPivot(['is_default', 'sort_order'])
            ->withTimestamps();
    }

    /**
     * URL the player app should load. Uploaded files are served from storage.
     */
    public function getResolvedUrlAttribute(): ?string
    {
        if ($this->file_path) {
            return Storage::disk('public')->url($this->file_path);
        }

        return $this->url;
    }
}
