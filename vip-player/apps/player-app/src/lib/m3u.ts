import { isAdultName } from './adult';
import { Channel, ContentKind } from './types';

/**
 * Parse an M3U/M3U8 playlist into channels.
 * Classifies entries as live / movie / series using URL and group heuristics.
 */
export function parseM3U(text: string): Channel[] {
  const lines = text.split(/\r?\n/);
  const channels: Channel[] = [];

  let pendingName = '';
  let pendingLogo: string | undefined;
  let pendingGroup = 'Uncategorized';

  for (const rawLine of lines) {
    const line = rawLine.trim();
    if (!line) continue;

    if (line.startsWith('#EXTINF')) {
      pendingLogo = matchAttr(line, 'tvg-logo');
      pendingGroup = matchAttr(line, 'group-title') || 'Uncategorized';
      const commaIndex = line.lastIndexOf(',');
      pendingName = commaIndex >= 0 ? line.slice(commaIndex + 1).trim() : 'Unknown';
    } else if (!line.startsWith('#')) {
      const url = line;
      const kind = classify(url, pendingGroup);
      channels.push({
        id: `m3u:${channels.length}:${url.slice(-40)}`,
        name: pendingName || 'Unknown',
        logo: pendingLogo,
        group: pendingGroup,
        url,
        kind,
        isAdult: isAdultName(pendingGroup) || isAdultName(pendingName),
      });
      pendingName = '';
      pendingLogo = undefined;
      pendingGroup = 'Uncategorized';
    }
  }

  return channels;
}

function matchAttr(line: string, attr: string): string | undefined {
  const match = line.match(new RegExp(`${attr}="([^"]*)"`));
  return match?.[1] || undefined;
}

function classify(url: string, group: string): ContentKind {
  const lowerUrl = url.toLowerCase();
  const lowerGroup = group.toLowerCase();

  if (lowerUrl.includes('/series/') || /\bseries\b|\bseason\b/.test(lowerGroup)) {
    return 'series';
  }

  if (
    lowerUrl.includes('/movie/') ||
    /\bmovies?\b|\bvod\b|\bfilms?\b|\bcinema\b/.test(lowerGroup) ||
    /\.(mp4|mkv|avi)$/.test(lowerUrl)
  ) {
    return 'movie';
  }

  return 'live';
}
