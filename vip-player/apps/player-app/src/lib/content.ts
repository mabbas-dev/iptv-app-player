import { isAdultName } from './adult';
import { parseM3U } from './m3u';
import { Category, Channel, ContentBundle, RemotePlaylist } from './types';
import { SyncProgressCallback } from './syncTypes';
import { loadXtreamEpisodes, loadXtreamMovies, loadXtreamLive, loadXtreamSeries } from './xtream';

/**
 * Load playlist content with per-section progress updates.
 */
export async function loadPlaylistContentProgressive(
  playlist: RemotePlaylist,
  onProgress: SyncProgressCallback,
  deviceCode: string,
): Promise<ContentBundle> {
  onProgress({ live: 'loading', vod: 'waiting', series: 'waiting', guide: 'waiting', message: 'Loading LIVE TV…' });

  let liveChannels: Channel[] = [];
  let movieChannels: Channel[] = [];
  let seriesChannels: Channel[] = [];

  if (playlist.type === 'xtream') {
    if (!playlist.server_url || !playlist.username || !playlist.password) {
      throw new Error('This Xtream playlist is missing login details.');
    }
    const config = {
      deviceCode,
      playlistId: playlist.id,
    };

    liveChannels = await loadXtreamLive(config);
    onProgress({ live: 'done', vod: 'loading', message: 'Loading VOD…' });

    movieChannels = await loadXtreamMovies(config);
    onProgress({ vod: 'done', series: 'loading', message: 'Loading SERIES…' });

    seriesChannels = await loadXtreamSeries(config);
    onProgress({ series: 'done', guide: 'loading', message: 'Loading TV Guide…' });
  } else if (playlist.type === 'direct') {
    liveChannels = [
      {
        id: `direct:${playlist.id}`,
        name: playlist.name,
        group: 'Direct',
        url: playlist.url ?? '',
        kind: 'live',
        isAdult: false,
      },
    ];
    onProgress({ live: 'done', vod: 'done', series: 'done', guide: 'loading', message: 'Loading TV Guide…' });
  } else {
    if (!playlist.url) throw new Error('This playlist has no URL.');
    const { api } = await import('./api');
    const result = await api.getPlaylistM3u(deviceCode, playlist.id);
    const text = result.data.text;
    const channels = parseM3U(text);
    liveChannels = channels.filter((c) => c.kind === 'live');
    onProgress({ live: 'done', vod: 'loading', message: 'Loading VOD…' });
    movieChannels = channels.filter((c) => c.kind === 'movie');
    onProgress({ vod: 'done', series: 'loading', message: 'Loading SERIES…' });
    seriesChannels = channels.filter((c) => c.kind === 'series');
    onProgress({ series: 'done', guide: 'loading', message: 'Loading TV Guide…' });
  }

  await loadGuide(playlist);
  onProgress({ guide: 'done', message: 'Saving content…' });

  const bundle = {
    live: groupChannels(liveChannels),
    movies: groupChannels(movieChannels),
    series: groupChannels(seriesChannels),
  };

  if (
    playlist.type === 'xtream' &&
    bundle.live.length === 0 &&
    bundle.movies.length === 0 &&
    bundle.series.length === 0
  ) {
    throw new Error('Cannot reach your IPTV server. Check host, username, and password, then re-upload.');
  }

  return bundle;
}

export async function loadPlaylistContent(
  playlist: RemotePlaylist,
  deviceCode: string,
): Promise<ContentBundle> {
  return loadPlaylistContentProgressive(playlist, () => {}, deviceCode);
}

async function loadGuide(playlist: RemotePlaylist): Promise<void> {
  if (!playlist.epg_url) return;
  try {
    await fetch(playlist.epg_url, { method: 'HEAD' });
  } catch {
    // Guide optional — ignore failures.
  }
}

function groupChannels(channels: Channel[]): Category[] {
  const map = new Map<string, Channel[]>();

  for (const channel of channels) {
    const list = map.get(channel.group) ?? [];
    list.push(channel);
    map.set(channel.group, list);
  }

  return Array.from(map.entries())
    .map(([name, list]) => ({
      name,
      isAdult: isAdultName(name),
      channels: list,
    }))
    .sort((a, b) => a.name.localeCompare(b.name));
}

export function searchBundle(bundle: ContentBundle, query: string, includeAdult: boolean): Channel[] {
  const q = query.trim().toLowerCase();
  if (q.length < 2) return [];

  const all = [
    ...bundle.live.flatMap((c) => c.channels),
    ...bundle.movies.flatMap((c) => c.channels),
    ...bundle.series.flatMap((c) => c.channels),
  ];

  return all
    .filter((c) => (includeAdult || !c.isAdult) && c.name.toLowerCase().includes(q))
    .slice(0, 100);
}

export { loadXtreamEpisodes };
