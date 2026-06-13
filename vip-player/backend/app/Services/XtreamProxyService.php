<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class XtreamProxyService
{
    public function fetch(string $serverUrl, string $username, string $password, string $action, array $extra = []): array
    {
        $url = rtrim($serverUrl, '/').'/player_api.php?'.http_build_query([
            'username' => $username,
            'password' => $password,
            'action' => $action,
            ...$extra,
        ]);

        $response = Http::timeout(45)
            ->withHeaders([
                'User-Agent' => 'FOX-PLAYER/1.0',
                'Accept' => 'application/json',
            ])
            ->get($url);

        if (! $response->ok()) {
            throw new RuntimeException("IPTV server returned error ({$response->status()}).");
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}
