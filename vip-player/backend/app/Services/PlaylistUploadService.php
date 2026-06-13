<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Playlist;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Handles public user playlist uploads tied to a device MAC.
 * Re-uploading replaces the existing playlist — users cannot edit, only replace.
 */
class PlaylistUploadService
{
    public function upload(Device $device, array $data): Playlist
    {
        if ($device->mac_locked) {
            throw new RuntimeException('This device MAC is locked. Unlock it in the app settings before uploading.');
        }

        return DB::transaction(function () use ($device, $data) {
            // Remove any existing playlist for this device (replace, not edit).
            Playlist::where('device_id', $device->id)->delete();
            $device->playlists()->detach();

            $playlist = Playlist::create([
                'device_id' => $device->id,
                'name' => $data['name'] ?? 'My Playlist',
                'type' => $data['type'],
                'server_url' => $data['server_url'] ?? null,
                'username' => $data['username'] ?? null,
                'password' => $data['password'] ?? null,
                'url' => $data['url'] ?? null,
                'epg_url' => $data['epg_url'] ?? null,
                'reseller_id' => null,
                'is_active' => true,
                'uploaded_at' => now(),
            ]);

            $device->playlists()->attach($playlist->id, [
                'is_default' => true,
                'sort_order' => 0,
            ]);

            $device->update(['playlist_synced_at' => now()]);

            return $playlist;
        });
    }
}
