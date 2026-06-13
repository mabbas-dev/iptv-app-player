import { useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { Logo } from '../src/components/Logo';
import { useApp } from '../src/context/AppContext';
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

        if (!device.is_watchable) {
          if (!cancelled) router.replace('/activation');
          return;
        }

        if (!cancelled) router.replace('/sync');
      } catch (e: any) {
        if (!cancelled) {
          setError(e?.message ?? 'Could not connect. Check WiFi and API URL.');
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [attempt]);

  return (
    <View style={styles.container}>
      <Logo size={40} />
      <Text style={styles.tagline}>Premium media player</Text>

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
            <Text style={styles.retryText}>Retry</Text>
          </Focusable>
        </View>
      ) : (
        <ActivityIndicator color={colors.gold} size="large" style={{ marginTop: spacing.xl }} />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg, alignItems: 'center', justifyContent: 'center' },
  tagline: { color: colors.textMuted, marginTop: spacing.sm, fontSize: 12, letterSpacing: 3, textTransform: 'uppercase' },
  errorBox: { marginTop: spacing.xl, alignItems: 'center', paddingHorizontal: spacing.xl, gap: spacing.md },
  errorText: { color: colors.danger, textAlign: 'center' },
  retryButton: { backgroundColor: colors.gold, paddingHorizontal: spacing.xl, paddingVertical: spacing.sm, borderRadius: radius.md, borderWidth: 2, borderColor: 'transparent' },
  retryText: { color: colors.bg, fontWeight: '800' },
});
