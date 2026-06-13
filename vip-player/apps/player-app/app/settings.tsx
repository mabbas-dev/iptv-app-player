import { useRouter } from 'expo-router';
import React from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { useApp } from '../src/context/AppContext';
import { api } from '../src/lib/api';
import {
  clearWatchHistory,
} from '../src/lib/storage';
import { colors, radius, spacing } from '../src/lib/theme';

export default function SettingsScreen() {
  const router = useRouter();
  const { device, activePlaylist, syncedAt, refreshDevice, setDevice } = useApp();

  const items = [
    {
      title: 'Parental Lock',
      subtitle: device?.parental_lock_enabled ? 'Enabled (4-digit PIN)' : 'Disabled',
      icon: '🔒',
      onPress: () => router.push({ pathname: '/parental', params: { mode: 'manage' } }),
    },
    {
      title: 'Lock MAC Address',
      subtitle: device?.mac_locked ? 'Locked — uploads blocked' : 'Unlocked',
      icon: '🔐',
      onPress: () => router.push({ pathname: '/parental', params: { mode: 'maclock' } }),
    },
    {
      title: 'Resync Playlist',
      subtitle: syncedAt ? `Last sync: ${new Date(syncedAt).toLocaleString()}` : 'Not synced yet',
      icon: '🔄',
      onPress: () => router.push({ pathname: '/sync', params: { force: '1' } }),
    },
    {
      title: 'Upload Playlist',
      subtitle: 'Add or replace on website',
      icon: '📤',
      onPress: () => router.push('/activation'),
    },
    {
      title: 'Clear Watch History',
      subtitle: 'Remove recently watched',
      icon: '🗑️',
      onPress: () => {
        Alert.alert('Clear history?', 'Remove all recently watched items?', [
          { text: 'Cancel', style: 'cancel' },
          {
            text: 'Clear',
            style: 'destructive',
            onPress: () => clearWatchHistory(),
          },
        ]);
      },
    },
    {
      title: 'Support',
      subtitle: 'Contact us',
      icon: '💬',
      onPress: () => router.push('/support'),
    },
    {
      title: 'Refresh Account',
      subtitle: 'Re-check status',
      icon: '↻',
      onPress: async () => {
        await refreshDevice();
        await loadPlaylists();
      },
    },
  ];

  const expiry = device?.is_lifetime
    ? 'Lifetime'
    : device?.expires_at
      ? new Date(device.expires_at).toLocaleString()
      : '—';

  return (
    <ScrollView style={styles.container} contentContainerStyle={styles.scroll}>
      <Text style={styles.title}>SETTINGS</Text>

      <View style={styles.deviceCard}>
        <Text style={styles.deviceLabel}>DEVICE ID</Text>
        <Text style={styles.deviceCode}>{device?.device_code ?? '—'}</Text>
        <Text style={styles.metaText}>Status: {device?.status?.toUpperCase() ?? '—'}</Text>
        <Text style={styles.metaText}>Expires: {expiry}</Text>
        {activePlaylist ? (
          <Text style={styles.metaText}>Playlist: {activePlaylist.name}</Text>
        ) : null}
      </View>

      {items.map((item, index) => (
        <Focusable
          key={item.title}
          style={styles.item}
          onPress={item.onPress}
          hasTVPreferredFocus={index === 0}
        >
          <Text style={styles.itemIcon}>{item.icon}</Text>
          <View style={{ flex: 1 }}>
            <Text style={styles.itemTitle}>{item.title}</Text>
            <Text style={styles.itemSubtitle}>{item.subtitle}</Text>
          </View>
          <Text style={styles.chevron}>›</Text>
        </Focusable>
      ))}

      <View style={styles.legalBox}>
        <Text style={styles.legalText}>
          {device?.settings?.legal_disclaimer ??
            'FOX PLAYER is a media player only. Users must add their own legally authorized content.'}
        </Text>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  scroll: { padding: spacing.lg, paddingBottom: spacing.xxl },
  title: { color: colors.text, fontSize: 24, fontWeight: '900', marginBottom: spacing.lg },
  deviceCard: {
    backgroundColor: colors.surface,
    borderRadius: radius.lg,
    padding: spacing.lg,
    marginBottom: spacing.lg,
    borderWidth: 1,
    borderColor: colors.border,
  },
  deviceLabel: { color: colors.textMuted, fontSize: 10, letterSpacing: 2 },
  deviceCode: { color: colors.gold, fontSize: 16, fontWeight: '800', marginTop: 4 },
  metaText: { color: colors.textDim, fontSize: 12, marginTop: 2 },
  item: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  itemIcon: { fontSize: 22, marginRight: spacing.md },
  itemTitle: { color: colors.text, fontSize: 15, fontWeight: '700' },
  itemSubtitle: { color: colors.textMuted, fontSize: 11, marginTop: 2 },
  chevron: { color: colors.textMuted, fontSize: 22 },
  legalBox: {
    marginTop: spacing.xl,
    padding: spacing.md,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
  },
  legalText: { color: colors.textMuted, fontSize: 11, lineHeight: 18, textAlign: 'center' },
});
