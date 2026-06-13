import { useRouter } from 'expo-router';
import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { acceptDisclaimer } from '../src/lib/storage';
import { colors, radius, spacing } from '../src/lib/theme';

const DISCLAIMER_TEXT =
  'FOX PLAYER is a media player only. We do not provide, host, sell, or distribute any TV ' +
  'channels, movies, playlists, or media content. Users must add their own legally authorized content.';

export default function DisclaimerScreen() {
  const router = useRouter();

  return (
    <View style={styles.container}>
      <Text style={styles.heading}>Legal Disclaimer</Text>
      <View style={styles.card}>
        <Text style={styles.text}>{DISCLAIMER_TEXT}</Text>
        <Text style={[styles.text, { marginTop: spacing.sm }]}>
          By continuing, you confirm that any playlists or streams you add are content you are
          legally authorized to access.
        </Text>
      </View>

      <Focusable
        style={styles.button}
        hasTVPreferredFocus
        onPress={async () => {
          await acceptDisclaimer();
          router.replace('/');
        }}
      >
        <Text style={styles.buttonText}>I Understand & Agree</Text>
      </Focusable>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
  },
  heading: {
    color: colors.text,
    fontSize: 20,
    fontWeight: '900',
    marginBottom: spacing.sm,
  },
  card: {
    backgroundColor: colors.surface,
    borderRadius: radius.lg,
    padding: spacing.md,
    maxWidth: 560,
    borderWidth: 1,
    borderColor: colors.border,
  },
  text: {
    color: colors.textDim,
    fontSize: 12,
    lineHeight: 18,
    textAlign: 'center',
  },
  button: {
    marginTop: spacing.md,
    backgroundColor: colors.gold,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.sm,
    borderRadius: radius.md,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  buttonText: {
    color: colors.bg,
    fontWeight: '900',
    fontSize: 14,
  },
});
