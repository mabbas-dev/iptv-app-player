import React, { useEffect, useRef } from 'react';
import { Animated, StyleSheet, Text, View } from 'react-native';
import { colors, radius, shadows } from '../lib/theme';
import { Focusable } from './Focusable';

interface DashboardOrbProps {
  label: string;
  sublabel?: string;
  icon: string;
  accent: string;
  onPress: () => void;
  hasTVPreferredFocus?: boolean;
  delay?: number;
}

export function DashboardOrb({
  label,
  sublabel,
  icon,
  accent,
  onPress,
  hasTVPreferredFocus,
  delay = 0,
}: DashboardOrbProps) {
  const scale = useRef(new Animated.Value(0.85)).current;
  const opacity = useRef(new Animated.Value(0)).current;
  const pulse = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.spring(scale, { toValue: 1, friction: 6, tension: 80, useNativeDriver: true, delay }),
      Animated.timing(opacity, { toValue: 1, duration: 500, delay, useNativeDriver: true }),
    ]).start();

    const loop = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 1.06, duration: 1800, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 1, duration: 1800, useNativeDriver: true }),
      ]),
    );
    loop.start();
    return () => loop.stop();
  }, [delay, opacity, pulse, scale]);

  return (
    <Animated.View style={{ opacity, transform: [{ scale }] }}>
      <Focusable
        onPress={onPress}
        hasTVPreferredFocus={hasTVPreferredFocus}
        style={[styles.wrap, { borderColor: accent }]}
        focusedStyle={[shadows.glow, { borderColor: colors.goldLight }]}
      >
        <Animated.View style={[styles.ring, { borderColor: accent, transform: [{ scale: pulse }] }]} />
        <View style={[styles.core, { backgroundColor: `${accent}22` }]}>
          <Text style={[styles.icon, { color: accent }]}>{icon}</Text>
        </View>
        <Text style={styles.label}>{label}</Text>
        {sublabel ? <Text style={styles.sublabel}>{sublabel}</Text> : null}
      </Focusable>
    </Animated.View>
  );
}

const styles = StyleSheet.create({
  wrap: {
    alignItems: 'center',
    width: 150,
    paddingVertical: 12,
    borderWidth: 1,
    borderRadius: radius.xl,
    backgroundColor: colors.surfaceGlass,
  },
  ring: {
    position: 'absolute',
    top: 18,
    width: 92,
    height: 92,
    borderRadius: 46,
    borderWidth: 2,
    opacity: 0.5,
  },
  core: {
    width: 76,
    height: 76,
    borderRadius: 38,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
  },
  icon: { fontSize: 34 },
  label: {
    color: colors.text,
    fontWeight: '800',
    fontSize: 13,
    letterSpacing: 1.2,
  },
  sublabel: {
    color: colors.textMuted,
    fontSize: 10,
    marginTop: 2,
  },
});
