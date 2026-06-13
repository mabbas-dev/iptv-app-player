export function friendlyPlaylistError(error: unknown): string {
  const msg = error instanceof Error ? error.message : String(error ?? 'Unknown error');

  if (/deprecated|expo-file-system|getInfoAsync/i.test(msg)) {
    return 'App storage error. Please restart and try sync again.';
  }
  if (/network request failed|failed to fetch|cannot reach|not reachable|timeout/i.test(msg)) {
    return 'Your IPTV server is not reachable. Scan the QR code below and re-upload your playlist.';
  }
  if (/xtream request failed|could not download playlist/i.test(msg)) {
    return 'Playlist download failed. Your IPTV link may be wrong — re-upload on our website.';
  }
  if (/missing login|no url|incomplete/i.test(msg)) {
    return 'Playlist settings are incomplete. Re-upload your playlist on our website.';
  }
  if (msg.length > 140) {
    return 'Could not load playlist. Re-upload on our website using the QR code.';
  }
  return msg;
}
