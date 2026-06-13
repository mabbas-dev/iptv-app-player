import AsyncStorage from '@react-native-async-storage/async-storage';
import {
  clearContentCache as clearFileCache,
  getCachedContent,
  getContentSyncedAt,
  saveContentSections,
} from './contentStorage';
import { Category } from './types';

const KEYS = {
  disclaimer: 'vip:disclaimer_accepted',
  favorites: 'vip:favorites',
  recents: 'vip:recents',
  activePlaylist: 'vip:active_playlist_id',
};

export { getCachedContent, getContentSyncedAt };

export async function setCachedContent(
  playlistId: number,
  bundle: { live: Category[]; movies: Category[]; series: Category[] },
): Promise<void> {
  await saveContentSections(playlistId, bundle.live, bundle.movies, bundle.series);
}

export async function clearContentCache(): Promise<void> {
  await clearFileCache();
}

export async function isDisclaimerAccepted(): Promise<boolean> {
  return (await AsyncStorage.getItem(KEYS.disclaimer)) === '1';
}

export async function acceptDisclaimer(): Promise<void> {
  await AsyncStorage.setItem(KEYS.disclaimer, '1');
}

export async function getFavorites(): Promise<import('./types').Channel[]> {
  const raw = await AsyncStorage.getItem(KEYS.favorites);
  return raw ? JSON.parse(raw) : [];
}

export async function toggleFavorite(channel: import('./types').Channel): Promise<import('./types').Channel[]> {
  const favorites = await getFavorites();
  const index = favorites.findIndex((c) => c.id === channel.id);
  if (index >= 0) favorites.splice(index, 1);
  else favorites.unshift(channel);
  await AsyncStorage.setItem(KEYS.favorites, JSON.stringify(favorites.slice(0, 200)));
  return favorites;
}

export async function getRecents(): Promise<import('./types').Channel[]> {
  const raw = await AsyncStorage.getItem(KEYS.recents);
  return raw ? JSON.parse(raw) : [];
}

export async function addRecent(channel: import('./types').Channel): Promise<void> {
  const recents = await getRecents();
  const filtered = recents.filter((c) => c.id !== channel.id);
  filtered.unshift(channel);
  await AsyncStorage.setItem(KEYS.recents, JSON.stringify(filtered.slice(0, 50)));
}

export async function clearRecents(): Promise<void> {
  await AsyncStorage.removeItem(KEYS.recents);
}

export async function clearFavorites(): Promise<void> {
  await AsyncStorage.removeItem(KEYS.favorites);
}

export async function clearWatchHistory(): Promise<void> {
  await clearRecents();
}

export async function getActivePlaylistId(): Promise<number | null> {
  const raw = await AsyncStorage.getItem(KEYS.activePlaylist);
  return raw ? Number(raw) : null;
}

export async function setActivePlaylistId(id: number): Promise<void> {
  await AsyncStorage.setItem(KEYS.activePlaylist, String(id));
}
