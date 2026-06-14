<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Playlist;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Handles public user playlist uploads tied to a device MAC.
 * Users can add multiple playlists; re-uploading the same name replaces that playlist.
 */
class PlaylistUploadService
{
    public function upload(Device $device, array $data): Playlist
    {
        if ($device->mac_locked) {
            throw new RuntimeException('This device MAC is locked. Unlock it in the app settings before uploading.');
        }

        return DB::transaction(function () use ($device, $data) {
            $name = $data['name'] ?? 'My Playlist';
            $existing = $device->playlists()->where('name', $name)->first();

            if ($existing) {
                $existing->update([
                    'type' => $data['type'],
                    'server_url' => $data['server_url'] ?? null,
                    'username' => $data['username'] ?? null,
                    'password' => $data['password'] ?? null,
                    'url' => $data['url'] ?? null,
                    'epg_url' => $data['epg_url'] ?? null,
                    'is_active' => true,
                    'uploaded_at' => now(),
                    'expires_at' => $data['expires_at'] ?? $existing->expires_at,
                ]);

                $device->update(['playlist_synced_at' => null]);

                return $existing->fresh();
            }

            $sortOrder = (int) $device->playlists()->max('device_playlists.sort_order') + 1;
            $isFirst = $device->playlists()->count() === 0;

            $playlist = Playlist::create([
                'device_id' => $device->id,
                'name' => $name,
                'type' => $data['type'],
                'server_url' => $data['server_url'] ?? null,
                'username' => $data['username'] ?? null,
                'password' => $data['password'] ?? null,
                'url' => $data['url'] ?? null,
                'epg_url' => $data['epg_url'] ?? null,
                'reseller_id' => null,
                'is_active' => true,
                'uploaded_at' => now(),
                'expires_at' => $data['expires_at'] ?? null,
            ]);

            $device->playlists()->attach($playlist->id, [
                'is_default' => $isFirst,
                'sort_order' => $sortOrder,
            ]);

            $device->update(['playlist_synced_at' => null]);

            return $playlist;
        });
    }
}
