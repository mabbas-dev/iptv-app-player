<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Device;
use App\Models\Plan;
use App\Services\PlaylistUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class UploadController extends Controller
{
    public function show(Request $request): View
    {
        $mac = strtoupper(trim($request->query('mac', '')));
        $device = $mac ? Device::where('device_code', $mac)->first() : null;

        return view('upload', [
            'mac' => $mac,
            'device' => $device,
            'locked' => $device?->mac_locked ?? false,
            'recaptchaSiteKey' => AppSetting::get('recaptcha_site_key', ''),
            'siteName' => config('app.name', 'FOX PLAYER'),
        ]);
    }

    public function store(Request $request, PlaylistUploadService $upload): JsonResponse
    {
        $data = $request->validate([
            'device_code' => ['required', 'string'],
            'type' => ['required', 'in:xtream,m3u,m3u8,direct'],
            'name' => ['nullable', 'string', 'max:100'],
            'server_url' => ['required_if:type,xtream', 'nullable', 'url'],
            'username' => ['required_if:type,xtream', 'nullable', 'string', 'max:255'],
            'password' => ['required_if:type,xtream', 'nullable', 'string', 'max:255'],
            'url' => ['required_unless:type,xtream', 'nullable', 'url'],
            'epg_url' => ['nullable', 'url'],
            'recaptcha_token' => ['nullable', 'string'],
        ]);

        if (! $this->verifyRecaptcha($data['recaptcha_token'] ?? null)) {
            return response()->json(['success' => false, 'message' => 'reCAPTCHA verification failed.'], 422);
        }

        $device = Device::where('device_code', strtoupper($data['device_code']))->first();

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID not found. Open FOX PLAYER on your device and copy the Device ID.',
            ], 404);
        }

        try {
            $playlist = $upload->upload($device, $data);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Playlist uploaded successfully! Open FOX PLAYER and refresh to start watching.',
            'data' => [
                'playlist_id' => $playlist->id,
                'type' => $playlist->type,
                'uploaded_at' => $playlist->uploaded_at?->toIso8601String(),
            ],
        ]);
    }

    protected function verifyRecaptcha(?string $token): bool
    {
        $secret = AppSetting::get('recaptcha_secret_key');

        if (empty($secret)) {
            return true; // Skip when not configured in admin panel.
        }

        if (empty($token)) {
            return false;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
        ]);

        $result = $response->json();

        return ($result['success'] ?? false) && ($result['score'] ?? 0) >= 0.5;
    }
}
