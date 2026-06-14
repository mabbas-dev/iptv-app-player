import * as FileSystem from 'expo-file-system/legacy';
import AsyncStorage from '@react-native-async-storage/async-storage';

const KEY = 'fox:downloads';

export interface DownloadEntry {
  id: string;
  title: string;
  localUri: string;
  remoteUrl: string;
  sizeBytes?: number;
  createdAt: string;
}

export async function getDownloads(): Promise<DownloadEntry[]> {
  const raw = await AsyncStorage.getItem(KEY);
  return raw ? JSON.parse(raw) : [];
}

export async function downloadContent(
  id: string,
  title: string,
  remoteUrl: string,
  onProgress?: (pct: number) => void,
): Promise<DownloadEntry> {
  const dir = `${FileSystem.documentDirectory}downloads/`;
  const info = await FileSystem.getInfoAsync(dir);
  if (!info.exists) {
    await FileSystem.makeDirectoryAsync(dir, { intermediates: true });
  }

  const ext = remoteUrl.split('?')[0].split('.').pop()?.slice(0, 4) || 'mp4';
  const safeName = title.replace(/[^a-z0-9-_]+/gi, '_').slice(0, 40);
  const localUri = `${dir}${id}_${safeName}.${ext}`;

  const callback = onProgress
    ? (data: FileSystem.DownloadProgressData) => {
        if (data.totalBytesExpectedToWrite > 0) {
          onProgress(Math.round((data.totalBytesWritten / data.totalBytesExpectedToWrite) * 100));
        }
      }
    : undefined;

  const result = await FileSystem.createDownloadResumable(remoteUrl, localUri, {
    headers: { 'User-Agent': 'FOX-PLAYER/1.0' },
  }, callback).downloadAsync();

  if (!result?.uri) {
    throw new Error('Download failed. Check your connection and try again.');
  }

  const entry: DownloadEntry = {
    id,
    title,
    localUri: result.uri,
    remoteUrl,
    sizeBytes: result.headers?.['Content-Length']
      ? Number(result.headers['Content-Length'])
      : undefined,
    createdAt: new Date().toISOString(),
  };

  const list = await getDownloads();
  list.unshift(entry);
  await AsyncStorage.setItem(KEY, JSON.stringify(list.slice(0, 50)));
  return entry;
}
