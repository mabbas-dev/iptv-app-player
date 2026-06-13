import { useLocalSearchParams, useRouter } from 'expo-router';
import * as ScreenOrientation from 'expo-screen-orientation';
import { useVideoPlayer, VideoView } from 'expo-video';
import { useEvent } from 'expo';
import React, { useEffect, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { buildLiveStreamSource, buildVodStreamSource } from '../src/lib/stream';
import { colors, radius, spacing } from '../src/lib/theme';

export default function PlayerScreen() {
  const router = useRouter();
  const { url, title, isLive } = useLocalSearchParams<{
    url: string;
    title: string;
    isLive?: string;
  }>();
  const [landscape, setLandscape] = useState(false);

  const isLiveStream = isLive === '1';
  const source = isLiveStream
    ? buildLiveStreamSource(String(url))
    : buildVodStreamSource(String(url));

  const player = useVideoPlayer(source, (p) => {
    p.play();
  });

  const { status, error: playerError } = useEvent(player, 'statusChange', {
    status: player.status,
  });
  const error =
    status === 'error'
      ? (playerError?.message ?? 'This stream could not be played.')
      : null;

  const toggleLandscape = async () => {
    if (landscape) {
      await ScreenOrientation.lockAsync(ScreenOrientation.OrientationLock.PORTRAIT_UP);
      setLandscape(false);
    } else {
      await ScreenOrientation.lockAsync(ScreenOrientation.OrientationLock.LANDSCAPE);
      setLandscape(true);
    }
  };

  useEffect(() => {
    return () => {
      ScreenOrientation.lockAsync(ScreenOrientation.OrientationLock.LANDSCAPE).catch(() => {});
    };
  }, []);

  return (
    <View style={styles.container}>
      <VideoView
        player={player}
        style={StyleSheet.absoluteFill}
        contentFit="contain"
        nativeControls
      />

      <View style={styles.overlayTop} pointerEvents="box-none">
        <Focusable style={styles.backButton} onPress={() => router.back()}>
          <Text style={styles.backText}>‹ Back</Text>
        </Focusable>
        <Text style={styles.title} numberOfLines={1}>
          {title}
        </Text>
        <Focusable style={styles.landscapeBtn} onPress={toggleLandscape}>
          <Text style={styles.backText}>{landscape ? '▣' : '▭'}</Text>
        </Focusable>
      </View>

      {error ? (
        <View style={styles.errorBox}>
          <Text style={styles.errorText}>{error}</Text>
        </View>
      ) : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#000' },
  overlayTop: {
    position: 'absolute',
    top: spacing.lg,
    left: spacing.md,
    right: spacing.md,
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.sm,
  },
  backButton: {
    backgroundColor: 'rgba(0,0,0,0.6)',
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  landscapeBtn: {
    backgroundColor: 'rgba(0,0,0,0.6)',
    borderRadius: radius.md,
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  backText: { color: colors.text, fontWeight: '800' },
  title: {
    color: colors.text,
    fontSize: 16,
    fontWeight: '700',
    flex: 1,
    textShadowColor: 'rgba(0,0,0,0.8)',
    textShadowRadius: 4,
  },
  errorBox: {
    position: 'absolute',
    bottom: spacing.xl,
    left: spacing.xl,
    right: spacing.xl,
    backgroundColor: 'rgba(239,68,68,0.9)',
    borderRadius: radius.md,
    padding: spacing.md,
  },
  errorText: { color: '#fff', textAlign: 'center', fontWeight: '600' },
});
