import { useRouter } from 'expo-router';
import React from 'react';
import { StyleSheet, Text } from 'react-native';
import { t } from '../lib/i18n';
import { colors, radius, spacing } from '../lib/theme';
import { Focusable } from './Focusable';

interface FoxBackButtonProps {
  toHome?: boolean;
}

export function FoxBackButton({ toHome = false }: FoxBackButtonProps) {
  const router = useRouter();

  return (
    <Focusable
      style={styles.btn}
      onPress={() => (toHome ? router.replace('/home') : router.back())}
    >
      <Text style={styles.icon}>←</Text>
      <Text style={styles.text}>{toHome ? t('home') : t('back')}</Text>
    </Focusable>
  );
}

const styles = StyleSheet.create({
  btn: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    borderWidth: 1,
    borderColor: colors.gold,
  },
  icon: { color: colors.gold, fontSize: 16, fontWeight: '900' },
  text: { color: colors.text, fontWeight: '800', fontSize: 13 },
});
