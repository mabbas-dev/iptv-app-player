import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import { ActivityIndicator, FlatList, StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { useApp } from '../src/context/AppContext';
import { Episode } from '../src/lib/types';
import { loadXtreamEpisodes } from '../src/lib/xtream';
import { colors, radius, spacing } from '../src/lib/theme';

export default function EpisodesScreen() {
  const router = useRouter();
  const { seriesId, title } = useLocalSearchParams<{ seriesId: string; title: string }>();
  const { activePlaylist, device } = useApp();
  const [episodes, setEpisodes] = useState<Episode[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (!activePlaylist || !device?.device_code) {
      setError('Episodes are only available for Xtream playlists.');
      return;
    }

    loadXtreamEpisodes(
      {
        deviceCode: device.device_code,
        playlistId: activePlaylist.id,
      },
      String(seriesId),
    )
      .then(setEpisodes)
      .catch((e) => setError(e?.message ?? 'Could not load episodes.'));
  }, [seriesId, activePlaylist, device?.device_code]);

  return (
    <View style={styles.container}>
      <Text style={styles.title} numberOfLines={1}>
        {title}
      </Text>

      {error ? (
        <Text style={styles.error}>{error}</Text>
      ) : !episodes ? (
        <ActivityIndicator color={colors.gold} size="large" style={{ marginTop: spacing.xl }} />
      ) : (
        <FlatList
          data={episodes}
          keyExtractor={(item) => item.id}
          renderItem={({ item }) => (
            <Focusable
              style={styles.row}
              onPress={() =>
                router.push({
                  pathname: '/player',
                  params: { url: item.url, title: `${title} S${item.season}E${item.episode}` },
                })
              }
            >
              <View style={styles.badge}>
                <Text style={styles.badgeText}>
                  S{item.season}E{item.episode}
                </Text>
              </View>
              <Text style={styles.episodeTitle} numberOfLines={1}>
                {item.title}
              </Text>
            </Focusable>
          )}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    padding: spacing.md,
  },
  title: {
    color: colors.text,
    fontSize: 24,
    fontWeight: '900',
    marginVertical: spacing.md,
  },
  error: {
    color: colors.danger,
    marginTop: spacing.xl,
    textAlign: 'center',
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  badge: {
    backgroundColor: colors.gold,
    borderRadius: radius.sm,
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    marginRight: spacing.md,
  },
  badgeText: {
    color: colors.bg,
    fontWeight: '900',
    fontSize: 12,
  },
  episodeTitle: {
    color: colors.text,
    fontSize: 15,
    fontWeight: '600',
    flex: 1,
  },
});
