import { useRouter } from 'expo-router';
import React, { useEffect } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { DashboardOrb } from '../src/components/DashboardOrb';
import { Focusable } from '../src/components/Focusable';
import { GradientBackground } from '../src/components/GradientBackground';
import { SettingsIcon } from '../src/components/SettingsIcon';
import { useApp } from '../src/context/AppContext';
import { colors, radius, spacing } from '../src/lib/theme';

const MAIN_ORBS = [
  { key: 'live', label: 'LIVE TV', sub: 'Channels', icon: '▶', route: '/live', accent: colors.gold },
  { key: 'movies', label: 'VOD', sub: 'Movies', icon: '◎', route: '/movies', accent: colors.purple },
  { key: 'series', label: 'SERIES', sub: 'TV Shows', icon: '☰', route: '/series', accent: colors.green },
  { key: 'fav', label: 'FAVORITE', sub: 'Saved', icon: '★', route: '/live', accent: colors.cyan },
] as const;

export default function HomeScreen() {
  const router = useRouter();
  const { device, content } = useApp();

  useEffect(() => {
    if (device && !device.is_watchable) {
      router.replace('/activation');
    }
  }, [device?.is_watchable]);

  const expiryText = device?.is_lifetime
    ? 'LIFETIME'
    : device?.expires_at
      ? new Date(device.expires_at).toLocaleDateString()
      : null;

  const count = (key: string) => {
    if (!content) return '';
    if (key === 'live') return `${content.live.reduce((n, c) => n + c.channels.length, 0)}`;
    if (key === 'movies') return `${content.movies.reduce((n, c) => n + c.channels.length, 0)}`;
    if (key === 'series') return `${content.series.reduce((n, c) => n + c.channels.length, 0)}`;
    return '';
  };

  return (
    <View style={styles.container}>
      <GradientBackground />

      <View style={styles.topBar}>
        <View style={styles.topActions}>
          <MiniAction icon="⌕" label="SEARCH" onPress={() => router.push('/live')} />
          <MiniAction icon="⟳" label="UPDATE" onPress={() => router.push({ pathname: '/sync', params: { force: '1' } })} />
          <MiniAction label="SETTINGS" useSettingsIcon onPress={() => router.push('/settings')} />
        </View>
        {device ? (
          <View style={styles.statusPill}>
            <Text style={styles.statusText}>
              {device.status.toUpperCase()}
              {expiryText ? ` · ${expiryText}` : ''}
            </Text>
          </View>
        ) : null}
      </View>

      <View style={styles.center}>
        <View style={styles.orbRow}>
          {MAIN_ORBS.map((orb, i) => (
            <DashboardOrb
              key={orb.key}
              label={orb.label}
              sublabel={`${orb.sub}${count(orb.key) ? ` · ${count(orb.key)}` : ''}`}
              icon={orb.icon}
              accent={orb.accent}
              onPress={() => router.push(orb.route as any)}
              hasTVPreferredFocus={i === 0}
              delay={i * 80}
            />
          ))}
        </View>
      </View>

      <View style={styles.bottomBar}>
        <BottomTile icon="👤" label="ACCOUNT" onPress={() => router.push('/activation')} />
        <BottomTile icon="📻" label="RADIO" onPress={() => router.push('/live')} />
        <BottomTile label="SETTINGS" useSettingsIcon onPress={() => router.push('/settings')} />
      </View>
    </View>
  );
}

function MiniAction({
  icon,
  label,
  useSettingsIcon,
  onPress,
}: {
  icon?: string;
  label: string;
  useSettingsIcon?: boolean;
  onPress: () => void;
}) {
  return (
    <Focusable style={styles.miniAction} onPress={onPress}>
      {useSettingsIcon ? (
        <SettingsIcon size={15} />
      ) : (
        <Text style={styles.miniIcon}>{icon}</Text>
      )}
      <Text style={styles.miniLabel}>{label}</Text>
    </Focusable>
  );
}

function BottomTile({
  icon,
  label,
  useSettingsIcon,
  onPress,
}: {
  icon?: string;
  label: string;
  useSettingsIcon?: boolean;
  onPress: () => void;
}) {
  return (
    <Focusable style={styles.bottomTile} onPress={onPress}>
      {useSettingsIcon ? (
        <SettingsIcon size={18} />
      ) : (
        <Text style={styles.bottomIcon}>{icon}</Text>
      )}
      <Text style={styles.bottomLabel}>{label}</Text>
    </Focusable>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg },
  topBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    paddingTop: spacing.sm,
    gap: spacing.md,
  },
  topActions: { flexDirection: 'row', gap: spacing.sm },
  miniAction: {
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 5,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: colors.surfaceGlass,
    minWidth: 68,
  },
  miniIcon: { color: colors.text, fontSize: 14 },
  miniLabel: { color: colors.textMuted, fontSize: 8, fontWeight: '700', marginTop: 2, letterSpacing: 0.5 },
  statusPill: {
    borderRadius: radius.full,
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderWidth: 1,
    borderColor: colors.gold,
    backgroundColor: 'rgba(245,158,11,0.12)',
  },
  statusText: { color: colors.gold, fontWeight: '800', fontSize: 9 },
  center: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  orbRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.lg,
    paddingHorizontal: spacing.lg,
  },
  bottomBar: {
    flexDirection: 'row',
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
    paddingBottom: spacing.md,
    gap: spacing.md,
  },
  bottomTile: {
    alignItems: 'center',
    paddingVertical: 8,
    paddingHorizontal: 20,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radius.md,
    backgroundColor: colors.surfaceGlass,
  },
  bottomIcon: { fontSize: 18, color: colors.text },
  bottomLabel: { color: colors.textMuted, fontSize: 9, fontWeight: '800', marginTop: 3, letterSpacing: 0.8 },
});
