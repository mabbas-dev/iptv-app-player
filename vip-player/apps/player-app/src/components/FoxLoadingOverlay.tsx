import React from 'react';
import { ActivityIndicator, StyleSheet, Text, View } from 'react-native';
import { t } from '../lib/i18n';
import { colors } from '../lib/theme';

interface FoxLoadingOverlayProps {
  message?: string;
  visible?: boolean;
}

export function FoxLoadingOverlay({ message, visible = true }: FoxLoadingOverlayProps) {
  if (!visible) return null;

  return (
    <View style={styles.overlay}>
      <View style={styles.ring}>
        <ActivityIndicator size="large" color={colors.gold} />
      </View>
      <Text style={styles.text}>{message ?? t('loadingStream')}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  overlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(0,0,0,0.72)',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 16,
  },
  ring: {
    width: 72,
    height: 72,
    borderRadius: 36,
    borderWidth: 3,
    borderColor: colors.gold,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: 'rgba(255,140,0,0.08)',
  },
  text: {
    color: colors.text,
    fontWeight: '700',
    fontSize: 15,
  },
});
