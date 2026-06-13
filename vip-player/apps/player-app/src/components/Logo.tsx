import React from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { colors } from '../lib/theme';

export function Logo({ size = 36 }: { size?: number }) {
  return (
    <View style={styles.row}>
      <Text style={[styles.fox, { fontSize: size }]}>FOX</Text>
      <Text style={[styles.player, { fontSize: size }]}> PLAYER</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  fox: {
    color: colors.gold,
    fontWeight: '900',
    letterSpacing: 2,
  },
  player: {
    color: colors.text,
    fontWeight: '900',
    letterSpacing: 2,
  },
});
