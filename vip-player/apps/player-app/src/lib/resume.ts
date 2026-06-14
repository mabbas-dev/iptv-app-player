import AsyncStorage from '@react-native-async-storage/async-storage';

const KEY = 'fox:resume';

export interface ResumeEntry {
  channelId: string;
  title: string;
  url: string;
  positionMs: number;
  durationMs?: number;
  updatedAt: string;
}

export async function getResumeMap(): Promise<Record<string, ResumeEntry>> {
  const raw = await AsyncStorage.getItem(KEY);
  return raw ? JSON.parse(raw) : {};
}

export async function saveResume(entry: ResumeEntry): Promise<void> {
  const map = await getResumeMap();
  map[entry.channelId] = entry;
  const keys = Object.keys(map).sort(
    (a, b) => new Date(map[b].updatedAt).getTime() - new Date(map[a].updatedAt).getTime(),
  );
  const trimmed = keys.slice(0, 100).reduce<Record<string, ResumeEntry>>((acc, id) => {
    acc[id] = map[id];
    return acc;
  }, {});
  await AsyncStorage.setItem(KEY, JSON.stringify(trimmed));
}

export async function getResume(channelId: string): Promise<ResumeEntry | null> {
  const map = await getResumeMap();
  return map[channelId] ?? null;
}

export async function getRecentResumeList(): Promise<ResumeEntry[]> {
  const map = await getResumeMap();
  return Object.values(map).sort(
    (a, b) => new Date(b.updatedAt).getTime() - new Date(a.updatedAt).getTime(),
  );
}
