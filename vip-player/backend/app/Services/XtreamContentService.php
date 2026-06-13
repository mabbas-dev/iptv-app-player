<?php

namespace App\Services;

class XtreamContentService
{
    public function __construct(
        protected XtreamProxyService $proxy,
    ) {}

    public function liveChannels(string $serverUrl, string $username, string $password): array
    {
        $base = rtrim($serverUrl, '/');
        $categories = $this->proxy->fetch($serverUrl, $username, $password, 'get_live_categories');
        $streams = $this->proxy->fetch($serverUrl, $username, $password, 'get_live_streams');
        $categoryMap = $this->categoryMap($categories);
        $channels = [];

        foreach ($streams as $stream) {
            if (! is_array($stream)) {
                continue;
            }
            $group = $categoryMap[$stream['category_id'] ?? ''] ?? 'Live';
            $name = (string) ($stream['name'] ?? 'Unknown');
            $channels[] = [
                'id' => 'xt:live:'.($stream['stream_id'] ?? count($channels)),
                'name' => $name,
                'logo' => $stream['stream_icon'] ?? null,
                'group' => $group,
                'url' => "{$base}/live/{$username}/{$password}/".($stream['stream_id'] ?? '').'.m3u8',
                'kind' => 'live',
                'isAdult' => $this->isAdultName($group) || $this->isAdultName($name),
            ];
        }

        return $channels;
    }

    public function vodChannels(string $serverUrl, string $username, string $password): array
    {
        $base = rtrim($serverUrl, '/');
        $categories = $this->proxy->fetch($serverUrl, $username, $password, 'get_vod_categories');
        $streams = $this->proxy->fetch($serverUrl, $username, $password, 'get_vod_streams');
        $categoryMap = $this->categoryMap($categories);
        $channels = [];

        foreach ($streams as $stream) {
            if (! is_array($stream)) {
                continue;
            }
            $group = $categoryMap[$stream['category_id'] ?? ''] ?? 'Movies';
            $name = (string) ($stream['name'] ?? 'Unknown');
            $ext = $stream['container_extension'] ?? 'mp4';
            $channels[] = [
                'id' => 'xt:vod:'.($stream['stream_id'] ?? count($channels)),
                'name' => $name,
                'logo' => $stream['stream_icon'] ?? null,
                'group' => $group,
                'url' => "{$base}/movie/{$username}/{$password}/".($stream['stream_id'] ?? '').".{$ext}",
                'kind' => 'movie',
                'isAdult' => $this->isAdultName($group) || $this->isAdultName($name),
            ];
        }

        return $channels;
    }

    public function seriesChannels(string $serverUrl, string $username, string $password): array
    {
        $categories = $this->proxy->fetch($serverUrl, $username, $password, 'get_series_categories');
        $seriesList = $this->proxy->fetch($serverUrl, $username, $password, 'get_series');
        $categoryMap = $this->categoryMap($categories);
        $channels = [];

        foreach ($seriesList as $series) {
            if (! is_array($series)) {
                continue;
            }
            $group = $categoryMap[$series['category_id'] ?? ''] ?? 'Series';
            $name = (string) ($series['name'] ?? 'Unknown');
            $channels[] = [
                'id' => 'xt:series:'.($series['series_id'] ?? count($channels)),
                'name' => $name,
                'logo' => $series['cover'] ?? null,
                'group' => $group,
                'url' => '',
                'kind' => 'series',
                'seriesId' => (string) ($series['series_id'] ?? ''),
                'isAdult' => $this->isAdultName($group) || $this->isAdultName($name),
            ];
        }

        return $channels;
    }

    public function episodes(string $serverUrl, string $username, string $password, string $seriesId): array
    {
        $base = rtrim($serverUrl, '/');
        $info = $this->proxy->fetch($serverUrl, $username, $password, 'get_series_info', [
            'series_id' => $seriesId,
        ]);

        $episodes = [];
        $seasons = $info['episodes'] ?? [];

        if (! is_array($seasons)) {
            return [];
        }

        foreach ($seasons as $seasonKey => $seasonEpisodes) {
            if (! is_array($seasonEpisodes)) {
                continue;
            }
            foreach ($seasonEpisodes as $ep) {
                if (! is_array($ep)) {
                    continue;
                }
                $ext = $ep['container_extension'] ?? 'mp4';
                $episodes[] = [
                    'id' => (string) ($ep['id'] ?? count($episodes)),
                    'title' => $ep['title'] ?? ('Episode '.($ep['episode_num'] ?? '')),
                    'season' => (int) $seasonKey,
                    'episode' => (int) ($ep['episode_num'] ?? 0),
                    'url' => "{$base}/series/{$username}/{$password}/".($ep['id'] ?? '').".{$ext}",
                ];
            }
        }

        usort($episodes, fn ($a, $b) => $a['season'] <=> $b['season'] ?: $a['episode'] <=> $b['episode']);

        return $episodes;
    }

    protected function categoryMap(array $categories): array
    {
        $map = [];
        foreach ($categories as $category) {
            if (! is_array($category)) {
                continue;
            }
            $map[$category['category_id'] ?? ''] = $category['category_name'] ?? 'Unknown';
        }

        return $map;
    }

    protected function isAdultName(string $name): bool
    {
        return (bool) preg_match('/(adult|18\s*\+|xxx|x{3,}|porn|hot|mature|erotic)/i', $name);
    }
}
