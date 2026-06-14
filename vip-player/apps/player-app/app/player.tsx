import { useLocalSearchParams, useRouter } from 'expo-router';
import * as ScreenOrientation from 'expo-screen-orientation';
import { useVideoPlayer, VideoView } from 'expo-video';
import { useEvent } from 'expo';
import React, { useEffect, useRef, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { FoxLoadingOverlay } from '../src/components/FoxLoadingOverlay';
import { friendlyStreamError } from '../src/lib/errors';
import { t } from '../src/lib/i18n';
import { saveResume } from '../src/lib/resume';
import { buildLiveStreamSource, buildVodStreamSource } from '../src/lib/stream';
import { colors, radius, spacing } from '../src/lib/theme';

export default function PlayerScreen() {
  const router = useRouter();
  const { url, title, isLive, channelId, resumeMs } = useLocalSearchParams<{
    url: string;
    title: string;
    isLive?: string;
    channelId?: string;
    resumeMs?: string;
  }>();
  const [landscape, setLandscape] = useState(false);
  const [userError, setUserError] = useState<string | null>(null);
  const savedOnce = useRef(false);

  const isLiveStream = isLive === '1';
  const source = isLiveStream
    ? buildLiveStreamSource(String(url))
    : buildVodStreamSource(String(url));

  const player = useVideoPlayer(source, (p) => {
    const resume = Number(resumeMs ?? 0);
    if (!isLiveStream && resume > 3000) {
      p.currentTime = resume / 1000;
    }
    p.play();
  });

  const { status, error: playerError } = useEvent(player, 'statusChange', {
    status: player.status,
  });

  const isLoading = status === 'loading' || status === 'idle';
  const streamError =
    status === 'error'
      ? friendlyStreamError(playerError?.message ?? 'Stream error')
      : userError;

  useEffect(() => {
    if (status === 'error') {
      setUserError(friendlyStreamError(playerError?.message));
    }
  }, [status, playerError?.message]);

  useEffect(() => {
    if (isLiveStream || !channelId) return undefined;
    const timer = setInterval(() => {
      if (player.currentTime > 5) {
        saveResume({
          channelId: String(channelId),
          title: String(title),
          url: String(url),
          positionMs: Math.floor(player.currentTime * 1000),
          durationMs: player.duration ? Math.floor(player.duration * 1000) : undefined,
          updatedAt: new Date().toISOString(),
        }).catch(() => {});
      }
    }, 10000);
    return () => clearInterval(timer);
  }, [channelId, isLiveStream, player, title, url]);

  useEffect(() => {
    return () => {
      if (!isLiveStream && channelId && player.currentTime > 5 && !savedOnce.current) {
        savedOnce.current = true;
        saveResume({
          channelId: String(channelId),
          title: String(title),
          url: String(url),
          positionMs: Math.floor(player.currentTime * 1000),
          durationMs: player.duration ? Math.floor(player.duration * 1000) : undefined,
          updatedAt: new Date().toISOString(),
        }).catch(() => {});
      }
      ScreenOrientation.lockAsync(ScreenOrientation.OrientationLock.LANDSCAPE).catch(() => {});
    };
  }, []);

  const toggleLandscape = async () => {
    if (landscape) {
      await ScreenOrientation.lockAsync(ScreenOrientation.OrientationLock.PORTRAIT_UP);
      setLandscape(false);
    } else {
      await ScreenOrientation.lockAsync(ScreenOrientation.OrientationLock.LANDSCAPE);
      setLandscape(true);
    }
  };

  return (
    <View style={styles.container}>
      <VideoView
        player={player}
        style={StyleSheet.absoluteFill}
        contentFit="contain"
        nativeControls
      />

      <FoxLoadingOverlay visible={isLoading && !streamError} />

      <View style={styles.overlayTop} pointerEvents="box-none">
        <Focusable style={styles.backButton} onPress={() => router.back()}>
          <Text style={styles.backText}>← {t('back')}</Text>
        </Focusable>
        <Text style={styles.title} numberOfLines={1}>
          {title}
        </Text>
        <Focusable style={styles.landscapeBtn} onPress={toggleLandscape}>
          <Text style={styles.backText}>{landscape ? '▣' : '▭'}</Text>
        </Focusable>
      </View>

      {streamError ? (
        <View style={styles.errorBox}>
          <Text style={styles.errorText}>{streamError}</Text>
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
    borderColor: colors.gold,
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
    backgroundColor: 'rgba(239,68,68,0.92)',
    borderRadius: radius.md,
    padding: spacing.md,
  },
  errorText: { color: '#fff', textAlign: 'center', fontWeight: '700', fontSize: 14 },
});
