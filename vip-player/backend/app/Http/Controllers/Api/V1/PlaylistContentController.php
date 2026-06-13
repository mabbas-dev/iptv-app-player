<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Playlist;
use App\Services\XtreamContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PlaylistContentController extends Controller
{
    public function __construct(
        protected XtreamContentService $xtream,
    ) {}

    public function live(string $deviceCode, int $playlistId): JsonResponse
    {
        return $this->proxyResponse(function () use ($deviceCode, $playlistId) {
            $playlist = $this->resolveXtreamPlaylist($deviceCode, $playlistId);

            return $this->channelsResponse(
                $this->xtream->liveChannels(
                    $playlist->server_url,
                    $playlist->username,
                    $playlist->password,
                ),
            );
        });
    }

    public function vod(string $deviceCode, int $playlistId): JsonResponse
    {
        return $this->proxyResponse(function () use ($deviceCode, $playlistId) {
            $playlist = $this->resolveXtreamPlaylist($deviceCode, $playlistId);

            return $this->channelsResponse(
                $this->xtream->vodChannels(
                    $playlist->server_url,
                    $playlist->username,
                    $playlist->password,
                ),
            );
        });
    }

    public function series(string $deviceCode, int $playlistId): JsonResponse
    {
        return $this->proxyResponse(function () use ($deviceCode, $playlistId) {
            $playlist = $this->resolveXtreamPlaylist($deviceCode, $playlistId);

            return $this->channelsResponse(
                $this->xtream->seriesChannels(
                    $playlist->server_url,
                    $playlist->username,
                    $playlist->password,
                ),
            );
        });
    }

    public function episodes(string $deviceCode, int $playlistId, string $seriesId): JsonResponse
    {
        return $this->proxyResponse(function () use ($deviceCode, $playlistId, $seriesId) {
            $playlist = $this->resolveXtreamPlaylist($deviceCode, $playlistId);

            return response()->json([
                'success' => true,
                'data' => [
                    'episodes' => $this->xtream->episodes(
                        $playlist->server_url,
                        $playlist->username,
                        $playlist->password,
                        $seriesId,
                    ),
                ],
            ]);
        });
    }

    public function m3u(string $deviceCode, int $playlistId): JsonResponse
    {
        return $this->proxyResponse(function () use ($deviceCode, $playlistId) {
            $playlist = $this->resolvePlaylist($deviceCode, $playlistId);

            if (! in_array($playlist->type, [Playlist::TYPE_M3U, Playlist::TYPE_M3U8], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This playlist is not an M3U type.',
                ], 400);
            }

            $url = $playlist->resolved_url;
            if (! $url) {
                return response()->json([
                    'success' => false,
                    'message' => 'Playlist URL is missing.',
                ], 422);
            }

            $response = Http::timeout(60)
                ->withHeaders([
                    'User-Agent' => 'FOX-PLAYER/1.0',
                    'Accept' => '*/*',
                ])
                ->get($url);

            if (! $response->ok()) {
                throw new RuntimeException("Could not download playlist ({$response->status()}).");
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'text' => $response->body(),
                ],
            ]);
        });
    }

    protected function proxyResponse(callable $callback): JsonResponse
    {
        try {
            return $callback();
        } catch (RuntimeException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 502);
        }
    }

    protected function channelsResponse(array $channels): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'channels' => $channels,
            ],
        ]);
    }

    protected function resolveXtreamPlaylist(string $deviceCode, int $playlistId): Playlist
    {
        $playlist = $this->resolvePlaylist($deviceCode, $playlistId);

        if ($playlist->type !== Playlist::TYPE_XTREAM) {
            abort(400, 'This playlist is not an Xtream type.');
        }

        if (! $playlist->server_url || ! $playlist->username || ! $playlist->password) {
            abort(422, 'Xtream playlist is missing login details.');
        }

        return $playlist;
    }

    protected function resolvePlaylist(string $deviceCode, int $playlistId): Playlist
    {
        $device = Device::where('device_code', strtoupper($deviceCode))->firstOrFail();
        $device->refreshStatus();

        if (! $device->isWatchable()) {
            abort(403, 'Your trial or subscription has expired.');
        }

        return $device->playlists()
            ->where('playlists.id', $playlistId)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
