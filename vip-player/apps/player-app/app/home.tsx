import { useRouter } from 'expo-router';
import React, { useEffect } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { FoxBrand } from '../src/components/FoxBrand';
import { Focusable } from '../src/components/Focusable';
import { MacBadge } from '../src/components/MacBadge';
import { useApp } from '../src/context/AppContext';
import { t } from '../src/lib/i18n';
import { colors, radius, spacing } from '../src/lib/theme';

const CARDS = [
  { key: 'live', labelKey: 'liveTv', icon: '📺', route: '/live' },
  { key: 'movies', labelKey: 'movies', icon: '🎬', route: '/movies' },
  { key: 'series', labelKey: 'series', icon: '🎞️', route: '/series' },
  { key: 'fav', labelKey: 'favourites', icon: '★', route: '/favorites' },
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
    ? 'Lifetime'
    : device?.expires_at
      ? new Date(device.expires_at).toLocaleDateString()
      : '—';

  const isActive = device?.status === 'active' || device?.status === 'trial';

  return (
    <View style={styles.container}>
      <View style={styles.topRow}>
        <View />
        <MacBadge mac={device?.device_code} />
      </View>

      <FoxBrand height={56} centered />
      <View style={styles.statusRow}>
        <View style={styles.statusPill}>
          <Text style={styles.statusIcon}>✓</Text>
          <Text style={styles.statusText}>{isActive ? t('active') : device?.status?.toUpperCase()}</Text>
        </View>
        <View style={styles.statusPill}>
          <Text style={styles.statusIcon}>📅</Text>
          <Text style={styles.statusText}>{t('expiresOn')} {expiryText}</Text>
        </View>
      </View>

      <View style={styles.cardsRow}>
        {CARDS.map((card, i) => (
          <Focusable
            key={card.key}
            style={[styles.card, i === 0 && styles.cardFocused]}
            onPress={() => router.push(card.route as any)}
            hasTVPreferredFocus={i === 0}
          >
            <Text style={styles.cardIcon}>{card.icon}</Text>
            <Text style={styles.cardLabel}>{t(card.labelKey)}</Text>
          </Focusable>
        ))}
      </View>

      <View style={styles.actionsRow}>
        <ActionChip icon="⟳" label={t('refresh')} onPress={() => router.push({ pathname: '/sync', params: { force: '1' } })} />
        <ActionChip icon="👤" label={t('account')} onPress={() => router.push('/activation')} />
        <ActionChip icon="⚙" label={t('settings')} onPress={() => router.push('/settings')} />
      </View>

      <Text style={styles.disclaimer}>{t('disclaimer')}</Text>
    </View>
  );
}

function ActionChip({ icon, label, onPress }: { icon: string; label: string; onPress: () => void }) {
  return (
    <Focusable style={styles.actionChip} onPress={onPress}>
      <Text style={styles.actionIcon}>{icon}</Text>
      <Text style={styles.actionLabel}>{label}</Text>
    </Focusable>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    paddingHorizontal: spacing.xl,
    paddingVertical: spacing.md,
    justifyContent: 'space-between',
  },
  topRow: { flexDirection: 'row', justifyContent: 'flex-end' },
  statusRow: { flexDirection: 'row', justifyContent: 'center', gap: spacing.md, marginTop: spacing.md },
  statusPill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: colors.surface,
    borderRadius: radius.full,
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    borderWidth: 1,
    borderColor: colors.border,
  },
  statusIcon: { color: colors.gold, fontSize: 12 },
  statusText: { color: colors.text, fontWeight: '700', fontSize: 12 },
  cardsRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: spacing.lg,
    marginVertical: spacing.lg,
  },
  card: {
    width: 130,
    height: 130,
    borderRadius: radius.xl,
    backgroundColor: colors.surface,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
    gap: spacing.sm,
  },
  cardFocused: { borderColor: colors.gold },
  cardIcon: { fontSize: 34 },
  cardLabel: { color: colors.text, fontWeight: '900', fontSize: 14 },
  actionsRow: { flexDirection: 'row', justifyContent: 'center', gap: spacing.md },
  actionChip: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    backgroundColor: colors.surface,
    borderRadius: radius.full,
    paddingHorizontal: spacing.lg,
    paddingVertical: 10,
    borderWidth: 1,
    borderColor: colors.border,
  },
  actionIcon: { color: colors.gold, fontSize: 14 },
  actionLabel: { color: colors.text, fontWeight: '700', fontSize: 12 },
  disclaimer: {
    color: colors.gold,
    textAlign: 'center',
    fontSize: 11,
    marginTop: spacing.sm,
  },
});
