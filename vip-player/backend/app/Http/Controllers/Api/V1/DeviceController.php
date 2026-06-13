<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Device;
use App\Services\DeviceActivationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeviceController extends Controller
{
    public function __construct(
        protected DeviceActivationService $activation,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'device_uuid' => ['required', 'uuid'],
            'platform' => ['required', 'in:android,android_tv'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ]);

        $device = Device::firstOrCreate(
            ['device_uuid' => $data['device_uuid']],
            [
                'device_code' => Device::generateDeviceCode(),
                'platform' => $data['platform'],
                'app_version' => $data['app_version'] ?? null,
                'status' => Device::STATUS_NEW,
            ],
        );

        $device->update([
            'platform' => $data['platform'],
            'app_version' => $data['app_version'] ?? $device->app_version,
            'last_seen_at' => now(),
        ]);

        // Auto-start free trial for brand-new devices.
        if ($device->wasRecentlyCreated || $device->status === Device::STATUS_NEW) {
            $trialDays = (int) AppSetting::get('trial_days', 7);
            if ($trialDays > 0) {
                try {
                    $this->activation->startTrial($device->fresh(), $trialDays);
                    $device->refresh();
                } catch (\Throwable) {
                    // Trial already started or device not eligible.
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $this->devicePayload($device->fresh()),
        ], $device->wasRecentlyCreated ? 201 : 200);
    }

    public function status(string $deviceCode): JsonResponse
    {
        $device = $this->findDevice($deviceCode);
        $device->refreshStatus();
        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => $this->devicePayload($device->fresh()),
        ]);
    }

    public function playlists(string $deviceCode): JsonResponse
    {
        $device = $this->findDevice($deviceCode);
        $device->refreshStatus();

        if (! $device->isWatchable()) {
            return response()->json([
                'success' => false,
                'message' => $this->expiryMessage($device),
                'data' => [
                    'status' => $device->status,
                    'activation_url' => url('/activation?mac=' . urlencode($device->device_code)),
                ],
            ], 403);
        }

        $playlists = $device->playlists()
            ->where('is_active', true)
            ->get()
            ->map(fn ($playlist) => [
                'id' => $playlist->id,
                'name' => $playlist->name,
                'type' => $playlist->type,
                'server_url' => $playlist->server_url,
                'username' => $playlist->username,
                'password' => $playlist->password,
                'url' => $playlist->resolved_url,
                'epg_url' => $playlist->epg_url,
                'is_default' => (bool) $playlist->pivot->is_default,
                'sort_order' => $playlist->pivot->sort_order,
                'uploaded_at' => $playlist->uploaded_at?->toIso8601String(),
                'synced_at' => $device->playlist_synced_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $playlists,
        ]);
    }

    public function markSynced(string $deviceCode): JsonResponse
    {
        $device = $this->findDevice($deviceCode);
        $device->update(['playlist_synced_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => ['synced_at' => $device->playlist_synced_at->toIso8601String()],
        ]);
    }

    public function setParentalLock(Request $request, string $deviceCode): JsonResponse
    {
        $device = $this->findDevice($deviceCode);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'pin' => ['required_if:enabled,true', 'nullable', 'digits:4'],
            'current_pin' => ['nullable', 'digits:4'],
        ]);

        if ($device->parental_pin_hash) {
            if (empty($data['current_pin']) || ! Hash::check($data['current_pin'], $device->parental_pin_hash)) {
                return response()->json(['success' => false, 'message' => 'Current PIN is incorrect.'], 422);
            }
        }

        $device->update([
            'parental_lock_enabled' => $data['enabled'],
            'parental_pin_hash' => ! empty($data['pin']) ? Hash::make($data['pin']) : $device->parental_pin_hash,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Parental lock updated.',
            'data' => ['parental_lock_enabled' => $device->parental_lock_enabled],
        ]);
    }

    public function setMacLock(Request $request, string $deviceCode): JsonResponse
    {
        $device = $this->findDevice($deviceCode);

        $data = $request->validate([
            'locked' => ['required', 'boolean'],
            'pin' => ['nullable', 'digits:4'],
        ]);

        if ($device->parental_pin_hash && $data['locked']) {
            if (empty($data['pin']) || ! Hash::check($data['pin'], $device->parental_pin_hash)) {
                return response()->json(['success' => false, 'message' => 'Enter your parental PIN to lock MAC.'], 422);
            }
        }

        $device->update(['mac_locked' => $data['locked']]);

        return response()->json([
            'success' => true,
            'message' => $data['locked'] ? 'MAC locked. Playlist cannot be changed on the website.' : 'MAC unlocked.',
            'data' => ['mac_locked' => $device->mac_locked],
        ]);
    }

    public function verifyParentalPin(Request $request, string $deviceCode): JsonResponse
    {
        $device = $this->findDevice($deviceCode);

        $data = $request->validate([
            'pin' => ['required', 'digits:4'],
        ]);

        $valid = $device->parental_pin_hash && Hash::check($data['pin'], $device->parental_pin_hash);

        return response()->json([
            'success' => $valid,
            'message' => $valid ? 'PIN verified.' : 'Incorrect PIN.',
        ], $valid ? 200 : 422);
    }

    protected function findDevice(string $deviceCode): Device
    {
        return Device::where('device_code', strtoupper($deviceCode))->firstOrFail();
    }

    protected function expiryMessage(Device $device): string
    {
        $url = url('/activation?mac=' . urlencode($device->device_code));

        return "Your trial or subscription has expired. Please renew at {$url}";
    }

    protected function devicePayload(Device $device): array
    {
        $device->refreshStatus();
        $baseUrl = rtrim(AppSetting::get('site_url', config('app.url')), '/');
        $uploadUrl = $baseUrl.'/upload?mac='.urlencode($device->device_code);
        $activationUrl = $baseUrl.'/activation?mac='.urlencode($device->device_code);

        return [
            'device_code' => $device->device_code,
            'device_uuid' => $device->device_uuid,
            'platform' => $device->platform,
            'status' => $device->status,
            'is_watchable' => $device->isWatchable(),
            'is_lifetime' => (bool) $device->is_lifetime,
            'trial_ends_at' => $device->trial_ends_at?->toIso8601String(),
            'subscription_ends_at' => $device->subscription_ends_at?->toIso8601String(),
            'expires_at' => $device->is_lifetime ? null : $device->expiresAt()?->toIso8601String(),
            'parental_lock_enabled' => (bool) $device->parental_lock_enabled,
            'has_parental_pin' => (bool) $device->parental_pin_hash,
            'mac_locked' => (bool) $device->mac_locked,
            'playlists_count' => $device->playlists()->count(),
            'playlist_synced_at' => $device->playlist_synced_at?->toIso8601String(),
            'upload_url' => $uploadUrl,
            'activation_url' => $activationUrl,
            'settings' => [
                'support_message' => AppSetting::get('support_message'),
                'support_email' => AppSetting::get('support_email'),
                'support_whatsapp' => AppSetting::get('support_whatsapp'),
                'min_app_version' => AppSetting::get('min_app_version', '1.0.0'),
                'force_update' => AppSetting::get('force_update', '0') === '1',
                'legal_disclaimer' => AppSetting::get('legal_disclaimer'),
                'activation_url' => $activationUrl,
            ],
        ];
    }
}
