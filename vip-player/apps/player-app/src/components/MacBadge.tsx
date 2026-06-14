import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { t } from '../lib/i18n';
import { colors, radius, spacing } from '../lib/theme';

export function MacBadge({ mac }: { mac?: string | null }) {
  return (
    <View style={styles.badge}>
      <Text style={styles.label}>{t('yourMac')} </Text>
      <Text style={styles.value}>{mac ?? '··:··:··:··:··:··'}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: colors.border,
  },
  label: { color: colors.gold, fontWeight: '700', fontSize: 11 },
  value: { color: colors.text, fontWeight: '800', fontSize: 11, fontVariant: ['tabular-nums'] },
});
