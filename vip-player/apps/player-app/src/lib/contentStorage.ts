import AsyncStorage from '@react-native-async-storage/async-storage';
import * as FileSystem from 'expo-file-system/legacy';
import { Category, ContentBundle } from './types';

const CACHE_DIR = `${FileSystem.documentDirectory}vip-content/`;
const LEGACY_PREFIX = 'vip:content_cache:';
const SYNCED_AT_KEY = 'vip:content_synced_at';

async function ensureDir(): Promise<void> {
  const info = await FileSystem.getInfoAsync(CACHE_DIR);
  if (!info.exists) {
    await FileSystem.makeDirectoryAsync(CACHE_DIR, { intermediates: true });
  }
}

function sectionPath(playlistId: number, section: 'live' | 'movies' | 'series'): string {
  return `${CACHE_DIR}playlist_${playlistId}_${section}.json`;
}

export async function saveContentSections(
  playlistId: number,
  live: Category[],
  movies: Category[],
  series: Category[],
): Promise<void> {
  await ensureDir();
  await Promise.all([
    FileSystem.writeAsStringAsync(sectionPath(playlistId, 'live'), JSON.stringify(live)),
    FileSystem.writeAsStringAsync(sectionPath(playlistId, 'movies'), JSON.stringify(movies)),
    FileSystem.writeAsStringAsync(sectionPath(playlistId, 'series'), JSON.stringify(series)),
  ]);
  await AsyncStorage.setItem(SYNCED_AT_KEY, new Date().toISOString());
}

export async function getCachedContent(playlistId: number): Promise<ContentBundle | null> {
  await ensureDir();
  const liveInfo = await FileSystem.getInfoAsync(sectionPath(playlistId, 'live'));
  if (!liveInfo.exists) {
    return migrateLegacyCache(playlistId);
  }

  try {
    const [liveRaw, moviesRaw, seriesRaw] = await Promise.all([
      FileSystem.readAsStringAsync(sectionPath(playlistId, 'live')),
      FileSystem.readAsStringAsync(sectionPath(playlistId, 'movies')),
      FileSystem.readAsStringAsync(sectionPath(playlistId, 'series')),
    ]);

    return {
      live: JSON.parse(liveRaw),
      movies: JSON.parse(moviesRaw),
      series: JSON.parse(seriesRaw),
    };
  } catch {
    return null;
  }
}

async function migrateLegacyCache(playlistId: number): Promise<ContentBundle | null> {
  const legacyKey = `${LEGACY_PREFIX}${playlistId}`;
  const raw = await AsyncStorage.getItem(legacyKey);
  if (!raw) return null;

  try {
    const bundle: ContentBundle = JSON.parse(raw);
    await saveContentSections(playlistId, bundle.live, bundle.movies, bundle.series);
    await AsyncStorage.removeItem(legacyKey);
    return bundle;
  } catch {
    await AsyncStorage.removeItem(legacyKey);
    return null;
  }
}

export async function clearContentCache(): Promise<void> {
  const info = await FileSystem.getInfoAsync(CACHE_DIR);
  if (info.exists) {
    await FileSystem.deleteAsync(CACHE_DIR, { idempotent: true });
  }

  const keys = await AsyncStorage.getAllKeys();
  const legacy = keys.filter((k) => k.startsWith(LEGACY_PREFIX));
  if (legacy.length) await AsyncStorage.multiRemove(legacy);
  await AsyncStorage.removeItem(SYNCED_AT_KEY);
}

export async function getContentSyncedAt(): Promise<string | null> {
  return AsyncStorage.getItem(SYNCED_AT_KEY);
}
