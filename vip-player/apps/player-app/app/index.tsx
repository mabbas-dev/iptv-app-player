import { useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';
import { FoxBrand } from '../src/components/FoxBrand';
import { Focusable } from '../src/components/Focusable';
import { MacBadge } from '../src/components/MacBadge';
import { useApp } from '../src/context/AppContext';
import { friendlyPlaylistError } from '../src/lib/errors';
import { initLanguage, t } from '../src/lib/i18n';
import { isDisclaimerAccepted } from '../src/lib/storage';
import { colors, radius, spacing } from '../src/lib/theme';

export default function SplashScreen() {
  const router = useRouter();
  const { bootstrap } = useApp();
  const [error, setError] = useState<string | null>(null);
  const [attempt, setAttempt] = useState(0);

  useEffect(() => {
    let cancelled = false;

    (async () => {
      try {
        const accepted = await isDisclaimerAccepted();
        if (!accepted) {
          if (!cancelled) router.replace('/disclaimer');
          return;
        }

        const device = await bootstrap();
        await initLanguage(device.settings?.default_language);

        if (!device.is_watchable) {
          if (!cancelled) router.replace('/activation');
          return;
        }

        if (!cancelled) router.replace('/sync');
      } catch (e: any) {
        if (!cancelled) {
          setError(friendlyPlaylistError(e));
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [attempt]);

  return (
    <View style={styles.container}>
      <View style={styles.topRow}>
        <View />
        <MacBadge />
      </View>

      <FoxBrand height={72} centered />

      {error ? (
        <View style={styles.errorBox}>
          <Text style={styles.errorText}>{error}</Text>
          <Focusable
            style={styles.retryButton}
            onPress={() => {
              setError(null);
              setAttempt((a) => a + 1);
            }}
          >
            <Text style={styles.retryText}>{t('refresh')}</Text>
          </Focusable>
        </View>
      ) : (
        <View style={styles.loadingBox}>
          <ActivityIndicator color={colors.gold} size="large" />
          <Text style={styles.waitText}>{t('pleaseWait')}</Text>
        </View>
      )}

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
  loadingBox: { alignItems: 'center', gap: spacing.md },
  waitText: { color: colors.textMuted },
  errorBox: { alignItems: 'center', gap: spacing.md, paddingHorizontal: spacing.xl },
  errorText: { color: colors.danger, textAlign: 'center', fontWeight: '700' },
  retryButton: {
    backgroundColor: colors.gold,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
  },
  retryText: { color: colors.bgDeep, fontWeight: '900' },
  disclaimer: { color: colors.gold, textAlign: 'center', fontSize: 11 },
});
