import React, { useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { useApp } from '../src/context/AppContext';
import { RemotePlaylist } from '../src/lib/types';
import { colors, radius, spacing } from '../src/lib/theme';

export default function PlaylistsScreen() {
  const { playlists, activePlaylist, switchPlaylist, loadPlaylists } = useApp();
  const [switching, setSwitching] = useState<number | null>(null);
  const [refreshing, setRefreshing] = useState(false);

  const activate = async (playlist: RemotePlaylist) => {
    setSwitching(playlist.id);
    try {
      await switchPlaylist(playlist);
    } finally {
      setSwitching(null);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.title}>PLAYLISTS</Text>
      <Text style={styles.sub}>
        Playlists are managed by your reseller or admin and synced to this device.
      </Text>

      <FlatList
        data={playlists}
        keyExtractor={(item) => String(item.id)}
        refreshing={refreshing}
        onRefresh={async () => {
          setRefreshing(true);
          await loadPlaylists();
          setRefreshing(false);
        }}
        renderItem={({ item, index }) => {
          const isActive = item.id === activePlaylist?.id;
          return (
            <Focusable
              style={[styles.item, isActive && styles.itemActive]}
              onPress={() => activate(item)}
              hasTVPreferredFocus={index === 0}
            >
              <View style={{ flex: 1 }}>
                <Text style={styles.itemName}>{item.name}</Text>
                <Text style={styles.itemType}>
                  {item.type.toUpperCase()}
                  {item.is_default ? ' · default' : ''}
                </Text>
              </View>
              {switching === item.id ? (
                <ActivityIndicator color={colors.gold} />
              ) : isActive ? (
                <Text style={styles.activeBadge}>ACTIVE</Text>
              ) : null}
            </Focusable>
          );
        }}
        ListEmptyComponent={
          <Text style={styles.empty}>
            No playlists yet. Ask your reseller to assign one to your Device ID.
          </Text>
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    padding: spacing.lg,
  },
  title: {
    color: colors.text,
    fontSize: 26,
    fontWeight: '900',
  },
  sub: {
    color: colors.textMuted,
    fontSize: 13,
    marginTop: spacing.xs,
    marginBottom: spacing.lg,
  },
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
  itemActive: {
    borderColor: colors.gold,
  },
  itemName: {
    color: colors.text,
    fontSize: 16,
    fontWeight: '700',
  },
  itemType: {
    color: colors.textMuted,
    fontSize: 12,
    marginTop: 2,
  },
  activeBadge: {
    color: colors.gold,
    fontWeight: '900',
    fontSize: 12,
  },
  empty: {
    color: colors.textMuted,
    textAlign: 'center',
    marginTop: spacing.xxl,
  },
});
