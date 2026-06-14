export type DeviceStatus = 'new' | 'trial' | 'active' | 'expired' | 'blocked' | 'suspended';

export interface AppSettings {
  support_message: string | null;
  support_email: string | null;
  support_whatsapp: string | null;
  min_app_version: string;
  force_update: boolean;
  legal_disclaimer: string | null;
  activation_url?: string;
  default_language?: string;
}

export interface DeviceInfo {
  device_code: string;
  device_uuid: string;
  platform: string;
  status: DeviceStatus;
  is_watchable: boolean;
  is_lifetime: boolean;
  trial_ends_at: string | null;
  subscription_ends_at: string | null;
  expires_at: string | null;
  parental_lock_enabled: boolean;
  has_parental_pin: boolean;
  mac_locked: boolean;
  playlists_count: number;
  playlist_synced_at: string | null;
  upload_url: string;
  activation_url: string;
  settings: AppSettings;
}

export type PlaylistType = 'xtream' | 'm3u' | 'm3u8' | 'direct';

export interface RemotePlaylist {
  id: number;
  name: string;
  type: PlaylistType;
  server_url: string | null;
  username: string | null;
  password: string | null;
  url: string | null;
  epg_url: string | null;
  is_default: boolean;
  uploaded_at?: string | null;
  expires_at?: string | null;
  synced_at?: string | null;
}

export type ContentKind = 'live' | 'movie' | 'series';

export interface Channel {
  id: string;
  name: string;
  logo?: string;
  group: string;
  url: string;
  kind: ContentKind;
  /** Xtream series id, when kind === 'series' and playback needs an episode list */
  seriesId?: string;
  isAdult: boolean;
}

export interface Category {
  name: string;
  isAdult: boolean;
  channels: Channel[];
}

export interface ContentBundle {
  live: Category[];
  movies: Category[];
  series: Category[];
}

export interface Episode {
  id: string;
  title: string;
  season: number;
  episode: number;
  url: string;
}

export interface SupportTicket {
  id: number;
  subject: string;
  message: string;
  admin_reply: string | null;
  status: string;
  created_at: string;
}
