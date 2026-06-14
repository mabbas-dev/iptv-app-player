import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect, useMemo, useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';
import { FoxBrand } from '../src/components/FoxBrand';
import { Focusable } from '../src/components/Focusable';
import { MacBadge } from '../src/components/MacBadge';
import { useApp } from '../src/context/AppContext';
import { friendlyPlaylistError, isRetryableError } from '../src/lib/errors';
import { t } from '../src/lib/i18n';
import { clearContentCache } from '../src/lib/storage';
import { SyncProgress } from '../src/lib/syncTypes';
import { colors, radius, spacing } from '../src/lib/theme';

function progressPercent(progress: SyncProgress): number {
  const sections = [progress.live, progress.vod, progress.series, progress.guide] as const;
  const done = sections.filter((s) => s === 'done').length;
  const loading = sections.filter((s) => s === 'loading').length;
  return Math.min(100, Math.round(((done + loading * 0.5) / 4) * 100));
}

export default function SyncScreen() {
  const router = useRouter();
  const { force } = useLocalSearchParams<{ force?: string }>();
  const { loadPlaylists, syncProgress, loadingContent, device } = useApp();
  const [error, setError] = useState<string | null>(null);
  const [attempt, setAttempt] = useState(0);
  const pct = useMemo(() => progressPercent(syncProgress), [syncProgress]);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      setError(null);
      try {
        if (force === '1') await clearContentCache();
        const playlists = await loadPlaylists(force === '1');
        if (cancelled) return;
        if (playlists.length === 0) {
          router.replace('/activation');
          return;
        }
        setTimeout(() => {
          if (!cancelled) router.replace('/home');
        }, 600);
      } catch (e) {
        if (cancelled) return;
        const message = friendlyPlaylistError(e);
        if (isRetryableError(e) && attempt < 2) {
          setTimeout(() => setAttempt((a) => a + 1), 1200);
          return;
        }
        setError(message);
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [force, attempt]);

  return (
    <View style={styles.container}>
      <View style={styles.topRow}>
        <View />
        <MacBadge mac={device?.device_code} />
      </View>

      <FoxBrand height={64} centered />

      <View style={styles.loadingBox}>
        <Text style={styles.loadingTitle}>{t('loading')}</Text>
        <View style={styles.progressTrack}>
          <View style={[styles.progressFill, { width: `${pct}%` }]} />
        </View>
        <Text style={styles.waitText}>
          {loadingContent ? syncProgress.message || t('pleaseWait') : t('pleaseWait')}
        </Text>
        {error ? <Text style={styles.errorText}>{error}</Text> : null}
        {error ? (
          <Focusable style={styles.retryBtn} onPress={() => setAttempt((a) => a + 1)}>
            <Text style={styles.retryText}>{t('refresh')}</Text>
          </Focusable>
        ) : (
          <ActivityIndicator color={colors.gold} style={{ marginTop: spacing.md }} />
        )}
      </View>

      <Text style={styles.disclaimer}>{t('disclaimer')}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    padding: spacing.xl,
    justifyContent: 'space-between',
  },
  topRow: { flexDirection: 'row', justifyContent: 'flex-end' },
  loadingBox: {
    alignSelf: 'center',
    width: 420,
    borderRadius: radius.xl,
    borderWidth: 1,
    borderColor: colors.gold,
    backgroundColor: colors.surface,
    padding: spacing.xl,
    alignItems: 'center',
    gap: spacing.md,
  },
  loadingTitle: { color: colors.text, fontWeight: '900', fontSize: 18 },
  progressTrack: {
    width: '100%',
    height: 10,
    borderRadius: radius.full,
    backgroundColor: colors.surfaceLight,
    overflow: 'hidden',
  },
  progressFill: { height: '100%', backgroundColor: colors.gold, borderRadius: radius.full },
  waitText: { color: colors.textMuted, fontSize: 13 },
  errorText: { color: colors.danger, textAlign: 'center', fontWeight: '700' },
  retryBtn: {
    backgroundColor: colors.gold,
    borderRadius: radius.md,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.sm,
  },
  retryText: { color: colors.bgDeep, fontWeight: '900' },
  disclaimer: { color: colors.gold, textAlign: 'center', fontSize: 11 },
});
