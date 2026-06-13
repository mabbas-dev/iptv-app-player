import { DeviceInfo, Episode, RemotePlaylist, SupportTicket, Channel } from './types';

const BASE_URL = process.env.EXPO_PUBLIC_API_URL ?? 'http://192.168.1.3:8000/api/v1';

class ApiError extends Error {
  constructor(message: string, public status: number) {
    super(message);
  }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const response = await fetch(`${BASE_URL}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      ...options.headers,
    },
  });

  const json = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new ApiError(json.message ?? `Request failed (${response.status})`, response.status);
  }

  return json as T;
}

export const api = {
  registerDevice(deviceUuid: string, platform: string, appVersion: string) {
    return request<{ success: boolean; data: DeviceInfo }>('/devices/register', {
      method: 'POST',
      body: JSON.stringify({ device_uuid: deviceUuid, platform, app_version: appVersion }),
    });
  },

  getStatus(deviceCode: string) {
    return request<{ success: boolean; data: DeviceInfo }>(
      `/devices/${encodeURIComponent(deviceCode)}/status`,
    );
  },

  getPlaylists(deviceCode: string) {
    return request<{ success: boolean; data: RemotePlaylist[] }>(
      `/devices/${encodeURIComponent(deviceCode)}/playlists`,
    );
  },

  getPlaylistLive(deviceCode: string, playlistId: number) {
    return request<{ success: boolean; data: { channels: Channel[] } }>(
      `/devices/${encodeURIComponent(deviceCode)}/playlists/${playlistId}/content/live`,
    );
  },

  getPlaylistVod(deviceCode: string, playlistId: number) {
    return request<{ success: boolean; data: { channels: Channel[] } }>(
      `/devices/${encodeURIComponent(deviceCode)}/playlists/${playlistId}/content/vod`,
    );
  },

  getPlaylistSeries(deviceCode: string, playlistId: number) {
    return request<{ success: boolean; data: { channels: Channel[] } }>(
      `/devices/${encodeURIComponent(deviceCode)}/playlists/${playlistId}/content/series`,
    );
  },

  getPlaylistEpisodes(deviceCode: string, playlistId: number, seriesId: string) {
    return request<{ success: boolean; data: { episodes: Episode[] } }>(
      `/devices/${encodeURIComponent(deviceCode)}/playlists/${playlistId}/content/series/${encodeURIComponent(seriesId)}/episodes`,
    );
  },

  getPlaylistM3u(deviceCode: string, playlistId: number) {
    return request<{ success: boolean; data: { text: string } }>(
      `/devices/${encodeURIComponent(deviceCode)}/playlists/${playlistId}/content/m3u`,
    );
  },

  markSynced(deviceCode: string) {
    return request<{ success: boolean; data: { synced_at: string } }>(
      `/devices/${encodeURIComponent(deviceCode)}/synced`,
      { method: 'POST', body: '{}' },
    );
  },

  setParentalLock(deviceCode: string, enabled: boolean, pin?: string, currentPin?: string) {
    return request<{ success: boolean; message: string }>(
      `/devices/${encodeURIComponent(deviceCode)}/parental-lock`,
      { method: 'POST', body: JSON.stringify({ enabled, pin, current_pin: currentPin }) },
    );
  },

  setMacLock(deviceCode: string, locked: boolean, pin?: string) {
    return request<{ success: boolean; message: string }>(
      `/devices/${encodeURIComponent(deviceCode)}/mac-lock`,
      { method: 'POST', body: JSON.stringify({ locked, pin }) },
    );
  },

  verifyParentalPin(deviceCode: string, pin: string) {
    return request<{ success: boolean; message: string }>(
      `/devices/${encodeURIComponent(deviceCode)}/parental-lock/verify`,
      { method: 'POST', body: JSON.stringify({ pin }) },
    );
  },

  createSupportTicket(payload: {
    device_code?: string;
    name?: string;
    email?: string;
    subject: string;
    message: string;
  }) {
    return request<{ success: boolean; message: string }>('/support', {
      method: 'POST',
      body: JSON.stringify(payload),
    });
  },

  getSupportTickets(deviceCode: string) {
    return request<{ success: boolean; data: SupportTicket[] }>(
      `/devices/${encodeURIComponent(deviceCode)}/support`,
    );
  },
};

export { ApiError };
