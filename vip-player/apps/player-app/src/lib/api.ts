import { DeviceInfo, Episode, RemotePlaylist, SupportTicket, Channel } from './types';

const BASE_URL = process.env.EXPO_PUBLIC_API_URL ?? 'http://192.168.1.3:8000/api/v1';
const MAX_RETRIES = 3;

class ApiError extends Error {
  constructor(message: string, public status: number) {
    super(message);
  }
}

function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

async function request<T>(path: string, options: RequestInit = {}, attempt = 1): Promise<T> {
  try {
    const response = await fetch(`${BASE_URL}${path}`, {
      ...options,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...options.headers,
      },
    });

    const json = await response.json().catch(() => ({} as Record<string, unknown>));

    if (!response.ok) {
      const message =
        (typeof json.message === 'string' && json.message) ||
        (typeof json.error === 'string' && json.error) ||
        `Request failed (${response.status})`;

      const retryable = [408, 429, 500, 502, 503, 504].includes(response.status);
      if (retryable && attempt < MAX_RETRIES) {
        await sleep(800 * attempt);
        return request<T>(path, options, attempt + 1);
      }

      throw new ApiError(message, response.status);
    }

    return json as T;
  } catch (error) {
    if (error instanceof ApiError) throw error;
    if (attempt < MAX_RETRIES) {
      await sleep(800 * attempt);
      return request<T>(path, options, attempt + 1);
    }
    throw new ApiError('No internet connection. Check your WiFi and try again.', 0);
  }
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
