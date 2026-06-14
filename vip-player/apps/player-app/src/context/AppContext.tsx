import React, {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useRef,
  useState,
} from 'react';
import { api } from '../lib/api';
import { friendlyPlaylistError } from '../lib/errors';
import { loadPlaylistContentProgressive } from '../lib/content';
import { APP_VERSION, getDeviceUuid, getPlatformName } from '../lib/device';
import {
  getActivePlaylistId,
  getCachedContent,
  getContentSyncedAt,
  setActivePlaylistId,
  setCachedContent,
} from '../lib/storage';
import { INITIAL_SYNC_PROGRESS, SyncProgress } from '../lib/syncTypes';
import { ContentBundle, DeviceInfo, RemotePlaylist } from '../lib/types';

interface AppContextValue {
  device: DeviceInfo | null;
  playlists: RemotePlaylist[];
  activePlaylist: RemotePlaylist | null;
  content: ContentBundle | null;
  contentError: string | null;
  loadingContent: boolean;
  syncProgress: SyncProgress;
  syncedAt: string | null;
  parentalUnlocked: boolean;
  bootstrap: () => Promise<DeviceInfo>;
  refreshDevice: () => Promise<DeviceInfo | null>;
  loadPlaylists: (forceResync?: boolean) => Promise<RemotePlaylist[]>;
  resyncPlaylist: () => Promise<void>;
  switchPlaylist: (playlist: RemotePlaylist) => Promise<void>;
  setParentalUnlocked: (unlocked: boolean) => void;
  setDevice: (device: DeviceInfo) => void;
}

const AppContext = createContext<AppContextValue | null>(null);

export function AppProvider({ children }: { children: React.ReactNode }) {
  const [device, setDevice] = useState<DeviceInfo | null>(null);
  const [playlists, setPlaylists] = useState<RemotePlaylist[]>([]);
  const [activePlaylist, setActivePlaylist] = useState<RemotePlaylist | null>(null);
  const [content, setContent] = useState<ContentBundle | null>(null);
  const [contentError, setContentError] = useState<string | null>(null);
  const [loadingContent, setLoadingContent] = useState(false);
  const [syncProgress, setSyncProgress] = useState<SyncProgress>(INITIAL_SYNC_PROGRESS);
  const [syncedAt, setSyncedAt] = useState<string | null>(null);
  const [parentalUnlocked, setParentalUnlocked] = useState(false);
  const deviceRef = useRef<DeviceInfo | null>(null);

  const bootstrap = useCallback(async () => {
    const uuid = await getDeviceUuid();
    const result = await api.registerDevice(uuid, getPlatformName(), APP_VERSION);
    deviceRef.current = result.data;
    setDevice(result.data);
    const cachedSync = await getContentSyncedAt();
    setSyncedAt(cachedSync);
    return result.data;
  }, []);

  const refreshDevice = useCallback(async () => {
    const current = deviceRef.current;
    if (!current) return null;
    const result = await api.getStatus(current.device_code);
    deviceRef.current = result.data;
    setDevice(result.data);
    return result.data;
  }, []);

  const loadContentFor = useCallback(
    async (playlist: RemotePlaylist, forceResync = false) => {
      setLoadingContent(true);
      setContentError(null);
      setSyncProgress(INITIAL_SYNC_PROGRESS);

      try {
        if (playlist.expires_at && new Date(playlist.expires_at) < new Date()) {
          throw new Error('This playlist has expired. Upload a new one on our website.');
        }

        if (!forceResync) {
          const cached = await getCachedContent(playlist.id);
          if (cached) {
            setContent(cached);
            const cachedSync = await getContentSyncedAt();
            setSyncedAt(cachedSync);
            setSyncProgress({
              live: 'done',
              vod: 'done',
              series: 'done',
              guide: 'done',
              message: 'Loaded from cache',
            });
            setLoadingContent(false);
            return;
          }
        }

        const current = deviceRef.current;
        if (!current) {
          throw new Error('Device not registered. Restart the app and try again.');
        }

        const bundle = await loadPlaylistContentProgressive(playlist, (update) => {
          setSyncProgress((prev) => ({ ...prev, ...update }));
        }, current.device_code);

        setContent(bundle);
        await setCachedContent(playlist.id, bundle);

        await api.markSynced(current.device_code);
        setSyncedAt(new Date().toISOString());

        setSyncProgress({
          live: 'done',
          vod: 'done',
          series: 'done',
          guide: 'done',
          message: 'Sync complete',
        });
      } catch (error: any) {
        const message = friendlyPlaylistError(error);
        setContent(null);
        setContentError(message);
        setSyncProgress((prev) => ({
          ...prev,
          message,
        }));
        throw new Error(message);
      } finally {
        setLoadingContent(false);
      }
    },
    [],
  );

  const loadPlaylists = useCallback(
    async (forceResync = false) => {
      const current = deviceRef.current;
      if (!current) return [];

      const result = await api.getPlaylists(current.device_code);
      setPlaylists(result.data);

      if (result.data.length > 0) {
        const savedId = await getActivePlaylistId();
        const chosen =
          result.data.find((p) => p.id === savedId) ??
          result.data.find((p) => p.is_default) ??
          result.data[0];
        setActivePlaylist(chosen);
        await loadContentFor(chosen, forceResync);
      } else {
        setActivePlaylist(null);
        setContent(null);
      }

      return result.data;
    },
    [loadContentFor],
  );

  const resyncPlaylist = useCallback(async () => {
    if (activePlaylist) {
      await loadContentFor(activePlaylist, true);
    }
  }, [activePlaylist, loadContentFor]);

  const switchPlaylist = useCallback(
    async (playlist: RemotePlaylist) => {
      setActivePlaylist(playlist);
      await setActivePlaylistId(playlist.id);
      await loadContentFor(playlist, false);
    },
    [loadContentFor],
  );

  const updateDevice = useCallback((d: DeviceInfo) => {
    deviceRef.current = d;
    setDevice(d);
  }, []);

  const value = useMemo(
    () => ({
      device,
      playlists,
      activePlaylist,
      content,
      contentError,
      loadingContent,
      syncProgress,
      syncedAt,
      parentalUnlocked,
      bootstrap,
      refreshDevice,
      loadPlaylists,
      resyncPlaylist,
      switchPlaylist,
      setParentalUnlocked,
      setDevice: updateDevice,
    }),
    [
      device,
      playlists,
      activePlaylist,
      content,
      contentError,
      loadingContent,
      syncProgress,
      syncedAt,
      parentalUnlocked,
      bootstrap,
      refreshDevice,
      loadPlaylists,
      resyncPlaylist,
      switchPlaylist,
      updateDevice,
    ],
  );

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp(): AppContextValue {
  const context = useContext(AppContext);
  if (!context) throw new Error('useApp must be used inside AppProvider');
  return context;
}
