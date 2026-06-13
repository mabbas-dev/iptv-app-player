import React from 'react';
import { Image, StyleSheet, Text, View } from 'react-native';
import { Channel } from '../lib/types';
import { colors, radius, spacing } from '../lib/theme';
import { Focusable } from './Focusable';

interface ChannelItemProps {
  channel: Channel;
  onPress: () => void;
  onLongPress?: () => void;
  isFavorite?: boolean;
}

export function ChannelItem({ channel, onPress, onLongPress, isFavorite }: ChannelItemProps) {
  return (
    <Focusable style={styles.row} onPress={onPress} onLongPress={onLongPress}>
      {channel.logo ? (
        <Image source={{ uri: channel.logo }} style={styles.logo} resizeMode="contain" />
      ) : (
        <View style={[styles.logo, styles.logoPlaceholder]}>
          <Text style={styles.logoLetter}>{channel.name.charAt(0).toUpperCase()}</Text>
        </View>
      )}
      <View style={styles.info}>
        <Text style={styles.name} numberOfLines={1}>
          {channel.name}
        </Text>
        <Text style={styles.group} numberOfLines={1}>
          {channel.group}
        </Text>
      </View>
      {isFavorite ? <Text style={styles.star}>★</Text> : null}
    </Focusable>
  );
}

const styles = StyleSheet.create({
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.sm,
    marginBottom: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  logo: {
    width: 48,
    height: 48,
    borderRadius: radius.sm,
    backgroundColor: colors.surfaceLight,
  },
  logoPlaceholder: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoLetter: {
    color: colors.gold,
    fontSize: 20,
    fontWeight: '800',
  },
  info: {
    flex: 1,
    marginLeft: spacing.md,
  },
  name: {
    color: colors.text,
    fontSize: 15,
    fontWeight: '600',
  },
  group: {
    color: colors.textMuted,
    fontSize: 12,
    marginTop: 2,
  },
  star: {
    color: colors.gold,
    fontSize: 18,
    marginRight: spacing.sm,
  },
});
