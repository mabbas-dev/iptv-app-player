import React, { useEffect, useRef } from 'react';
import { Animated, StyleSheet, Text, View } from 'react-native';
import { SyncProgress, SyncSection, SyncSectionStatus } from '../lib/syncTypes';
import { colors, radius } from '../lib/theme';

const SECTIONS: { key: SyncSection; label: string }[] = [
  { key: 'live', label: 'LIVE TV' },
  { key: 'vod', label: 'VOD' },
  { key: 'series', label: 'SERIES' },
  { key: 'guide', label: 'GUIDE' },
];

function statusLabel(status: SyncSectionStatus): string {
  switch (status) {
    case 'done':
      return 'Completed!';
    case 'loading':
      return 'Loading…';
    case 'error':
      return 'Failed';
    default:
      return 'Waiting…';
  }
}

function statusColor(status: SyncSectionStatus): string {
  switch (status) {
    case 'done':
      return colors.success;
    case 'loading':
      return colors.cyan;
    case 'error':
      return colors.danger;
    default:
      return colors.textMuted;
  }
}

function SyncCell({ label, status }: { label: string; status: SyncSectionStatus }) {
  const pulse = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    if (status !== 'loading') return;
    const loop = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 0.55, duration: 700, useNativeDriver: true }),
        Animated.timing(pulse, { toValue: 1, duration: 700, useNativeDriver: true }),
      ]),
    );
    loop.start();
    return () => loop.stop();
  }, [pulse, status]);

  return (
    <View style={styles.cell}>
      <Text style={styles.cellHeader}>{label}</Text>
      <Animated.View style={[styles.cellBody, status === 'loading' && { opacity: pulse }]}>
        <Text style={[styles.cellStatus, { color: statusColor(status) }]}>{statusLabel(status)}</Text>
      </Animated.View>
    </View>
  );
}

export function SyncProgressPanel({ progress }: { progress: SyncProgress }) {
  const barWidth = useRef(new Animated.Value(0)).current;
  const doneCount = [progress.live, progress.vod, progress.series, progress.guide].filter(
    (s) => s === 'done',
  ).length;
  const pct = doneCount / 4;

  useEffect(() => {
    Animated.timing(barWidth, {
      toValue: pct,
      duration: 400,
      useNativeDriver: false,
    }).start();
  }, [barWidth, pct]);

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Update Media Contents</Text>

      <View style={styles.grid}>
        {SECTIONS.map(({ key, label }) => (
          <SyncCell key={key} label={label} status={progress[key]} />
        ))}
      </View>

      <Text style={styles.message}>{progress.message}</Text>

      <View style={styles.track}>
        <Animated.View
          style={[
            styles.fill,
            {
              width: barWidth.interpolate({
                inputRange: [0, 1],
                outputRange: ['0%', '100%'],
              }),
            },
          ]}
        />
      </View>
      <Text style={styles.wait}>PLEASE WAIT…</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, justifyContent: 'center', paddingHorizontal: 40 },
  title: {
    alignSelf: 'center',
    color: colors.text,
    fontSize: 22,
    fontWeight: '300',
    letterSpacing: 2,
    marginBottom: 28,
    paddingHorizontal: 24,
    paddingVertical: 8,
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderRadius: radius.sm,
  },
  grid: { flexDirection: 'row', gap: 10 },
  cell: { flex: 1, borderRadius: radius.md, overflow: 'hidden' },
  cellHeader: {
    backgroundColor: '#0b1f3f',
    color: colors.text,
    textAlign: 'center',
    fontWeight: '800',
    fontSize: 12,
    letterSpacing: 1,
    paddingVertical: 10,
  },
  cellBody: {
    backgroundColor: '#13294b',
    minHeight: 72,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 8,
  },
  cellStatus: { fontWeight: '700', fontSize: 13, textAlign: 'center' },
  message: {
    color: colors.textDim,
    textAlign: 'center',
    marginTop: 24,
    fontSize: 14,
    letterSpacing: 1,
  },
  track: {
    height: 8,
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderRadius: 4,
    marginTop: 28,
    overflow: 'hidden',
  },
  fill: {
    height: '100%',
    backgroundColor: colors.cyan,
    borderRadius: 4,
  },
  wait: {
    color: colors.textMuted,
    textAlign: 'center',
    marginTop: 14,
    letterSpacing: 3,
    fontSize: 12,
    fontWeight: '700',
  },
});
