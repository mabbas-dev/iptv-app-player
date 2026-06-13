<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function app(): BinaryFileResponse|RedirectResponse
    {
        $customUrl = AppSetting::get('apk_download_url');

        if ($customUrl && str_starts_with($customUrl, 'http') && ! $this->isLocalDownloadUrl($customUrl)) {
            return redirect()->away($customUrl);
        }

        $path = public_path('downloads/FOX-PLAYER.apk');

        if (! file_exists($path)) {
            $path = public_path('downloads/VIP-PLAYER.apk');
        }

        if (! file_exists($path)) {
            abort(404, 'App download is not available yet. Please contact support.');
        }

        return response()->download($path, 'FOX-PLAYER.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }

    private function isLocalDownloadUrl(string $url): bool
    {
        $normalized = rtrim($url, '/');
        $localRoutes = [
            rtrim(route('download.app', absolute: true), '/'),
            rtrim(url('/download/app'), '/'),
        ];

        return in_array($normalized, $localRoutes, true);
    }
}
