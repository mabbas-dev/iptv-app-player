import { ApiError } from './api';

const MESSAGES: Record<string, string> = {
  network: 'No internet connection. Check your WiFi and try again.',
  server: 'Our server is busy. Please wait a moment and try again.',
  playlist: 'Could not load your playlist. Re-upload it on our website and try again.',
  expired: 'Your subscription has expired. Renew to continue watching.',
  forbidden: 'Access denied. Your device may be expired or blocked.',
  stream: 'This stream could not be played. Try another channel.',
  empty: 'No channels found. Check your playlist details on our website.',
  iptv: 'Your IPTV server did not respond. Wait a moment and tap Refresh.',
};

export function friendlyPlaylistError(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 403) {
      return error.message.includes('expired') || error.message.includes('trial')
        ? error.message
        : MESSAGES.forbidden;
    }
    if (error.status === 502 || error.status === 503) {
      return error.message && !/server error/i.test(error.message)
        ? error.message
        : MESSAGES.iptv;
    }
    if (error.status >= 500) {
      return MESSAGES.server;
    }
    if (error.status === 404) {
      return 'Playlist not found. Re-upload on our website.';
    }
    if (error.message && error.message !== `Request failed (${error.status})`) {
      return error.message;
    }
  }

  const msg = error instanceof Error ? error.message : String(error ?? 'Unknown error');

  if (/403|forbidden|permission denied/i.test(msg)) return MESSAGES.forbidden;
  if (/expired|subscription|trial has ended/i.test(msg)) return msg;
  if (/network request failed|failed to fetch|cannot reach|timeout|econnrefused/i.test(msg)) {
    return MESSAGES.network;
  }
  if (/server error|internal server/i.test(msg)) return MESSAGES.server;
  if (/playlist sync failed|iptv server/i.test(msg)) return MESSAGES.iptv;
  if (/could not download playlist|xtream request failed/i.test(msg)) return MESSAGES.playlist;
  if (/missing login|no url|incomplete/i.test(msg)) return MESSAGES.playlist;
  if (/cannot reach your iptv server/i.test(msg)) return MESSAGES.iptv;
  if (/request failed \(\d+\)/i.test(msg)) return MESSAGES.server;
  if (/deprecated|expo-file-system|getInfoAsync/i.test(msg)) {
    return 'App storage error. Please restart and try again.';
  }
  if (msg.length > 120) return MESSAGES.playlist;

  return msg;
}

export function friendlyStreamError(error: unknown): string {
  const msg = error instanceof Error ? error.message : String(error ?? '');
  if (!msg || /unknown/i.test(msg)) return MESSAGES.stream;
  if (/403|forbidden/i.test(msg)) return 'Stream access denied by your provider.';
  if (/404|not found/i.test(msg)) return 'Stream not found. It may be offline.';
  if (/timeout|network|failed/i.test(msg)) return 'Stream timed out. Try again.';
  if (/codec|format|unsupported/i.test(msg)) return 'This video format is not supported on your device.';
  return MESSAGES.stream;
}

export function isRetryableError(error: unknown): boolean {
  if (error instanceof ApiError) {
    return [408, 429, 500, 502, 503, 504].includes(error.status);
  }
  const msg = error instanceof Error ? error.message : String(error ?? '');
  return /network|timeout|server error|busy|try again/i.test(msg);
}

export { MESSAGES };
