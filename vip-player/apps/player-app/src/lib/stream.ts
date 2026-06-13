/**
 * Normalize live stream URLs and add IPTV-friendly headers.
 * Fixes HTTP 405 errors on some live HLS providers.
 */
export function buildLiveStreamSource(url: string): { uri: string; headers: Record<string, string> } {
  let uri = url;

  // Some Xtream providers reject .m3u8 but accept .ts for live.
  if (uri.includes('/live/') && uri.endsWith('.m3u8')) {
    uri = uri.replace(/\.m3u8$/, '.ts');
  }

  return {
    uri,
    headers: {
      'User-Agent': 'VLC/3.0.0 LibVLC/3.0.0',
      Accept: '*/*',
    },
  };
}

export function buildVodStreamSource(url: string): { uri: string; headers?: Record<string, string> } {
  return {
    uri: url,
    headers: {
      'User-Agent': 'VLC/3.0.0 LibVLC/3.0.0',
    },
  };
}
