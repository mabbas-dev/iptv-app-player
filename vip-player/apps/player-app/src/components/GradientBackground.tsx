import React from 'react';
import { StyleSheet, View } from 'react-native';
import Svg, { Circle, Defs, LinearGradient, Rect, Stop } from 'react-native-svg';
import { colors } from '../lib/theme';

export function GradientBackground() {
  return (
    <View style={StyleSheet.absoluteFill} pointerEvents="none">
      <Svg width="100%" height="100%" style={StyleSheet.absoluteFill}>
        <Defs>
          <LinearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
            <Stop offset="0%" stopColor="#020617" />
            <Stop offset="45%" stopColor="#0f172a" />
            <Stop offset="100%" stopColor="#172554" />
          </LinearGradient>
          <LinearGradient id="glow" x1="0" y1="0" x2="1" y2="0">
            <Stop offset="0%" stopColor="rgba(34,211,238,0)" />
            <Stop offset="50%" stopColor="rgba(34,211,238,0.12)" />
            <Stop offset="100%" stopColor="rgba(59,130,246,0)" />
          </LinearGradient>
        </Defs>
        <Rect x="0" y="0" width="100%" height="100%" fill="url(#bg)" />
        <Rect x="0" y="35%" width="100%" height="30%" fill="url(#glow)" />
        <Circle cx="15%" cy="20%" r="120" fill="rgba(245,158,11,0.06)" />
        <Circle cx="85%" cy="75%" r="160" fill="rgba(59,130,246,0.08)" />
        <Circle cx="50%" cy="50%" r="220" fill="rgba(34,211,238,0.04)" />
      </Svg>
    </View>
  );
}
