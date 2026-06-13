import AsyncStorage from '@react-native-async-storage/async-storage';
import * as Crypto from 'expo-crypto';
import { Platform } from 'react-native';

const UUID_KEY = 'vip:device_uuid';

/**
 * Returns a stable random UUID for this install.
 * We never read the real hardware MAC address — the backend generates
 * a random MAC-style Device ID from this UUID registration.
 */
export async function getDeviceUuid(): Promise<string> {
  const existing = await AsyncStorage.getItem(UUID_KEY);
  if (existing) return existing;

  const uuid = Crypto.randomUUID();
  await AsyncStorage.setItem(UUID_KEY, uuid);
  return uuid;
}

export function getPlatformName(): string {
  if (Platform.isTV) return 'android_tv';
  return Platform.OS === 'android' ? 'android' : 'android';
}

export const APP_VERSION = '1.0.0';
