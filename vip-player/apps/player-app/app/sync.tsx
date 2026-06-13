import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { GradientBackground } from '../src/components/GradientBackground';
import { SyncProgressPanel } from '../src/components/SyncProgressPanel';
import { useApp } from '../src/context/AppContext';
import { friendlyPlaylistError } from '../src/lib/errors';
import { clearContentCache } from '../src/lib/storage';
import { colors } from '../src/lib/theme';

export default function SyncScreen() {
  const router = useRouter();
  const { force } = useLocalSearchParams<{ force?: string }>();
  const { loadPlaylists, syncProgress, loadingContent } = useApp();

  useEffect(() => {
    let cancelled = false;

    (async () => {
      try {
        if (force === '1') {
          await clearContentCache();
        }
        const playlists = await loadPlaylists(force === '1');
        if (cancelled) return;

        if (playlists.length === 0) {
          router.replace('/activation');
          return;
        }

        setTimeout(() => {
          if (!cancelled) router.replace('/home');
        }, 500);
      } catch (e) {
        if (cancelled) return;
        const message = friendlyPlaylistError(e);
        router.replace({
          pathname: '/activation',
          params: { playlistError: message },
        });
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [force]);

  return (
    <View style={styles.container}>
      <GradientBackground />
      <SyncProgressPanel progress={syncProgress} />
      {!loadingContent && syncProgress.message === 'Sync complete' ? (
        <Text style={styles.done}>Opening dashboard…</Text>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bgDeep },
  done: {
    position: 'absolute',
    bottom: 24,
    alignSelf: 'center',
    color: colors.success,
    fontWeight: '700',
  },
});
