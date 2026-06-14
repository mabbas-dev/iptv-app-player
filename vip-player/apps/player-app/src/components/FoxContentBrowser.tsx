import { useRouter } from 'expo-router';
import React, { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Image,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useApp } from '../context/AppContext';
import { downloadContent } from '../lib/downloads';
import { t } from '../lib/i18n';
import { getResume } from '../lib/resume';
import { addRecent, toggleFavorite } from '../lib/storage';
import { Category, Channel } from '../lib/types';
import { colors, radius, spacing } from '../lib/theme';
import { FoxBackButton } from './FoxBackButton';
import { FoxBrand } from './FoxBrand';
import { Focusable } from './Focusable';
import { MacBadge } from './MacBadge';

interface FoxContentBrowserProps {
  title: string;
  categories: Category[];
  layout: 'live' | 'grid';
  emptyMessage?: string;
}

export function FoxContentBrowser({ title, categories, layout, emptyMessage }: FoxContentBrowserProps) {
  const router = useRouter();
  const { device, parentalUnlocked, loadingContent, contentError } = useApp();
  const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);
  const [selectedChannel, setSelectedChannel] = useState<Channel | null>(null);
  const [query, setQuery] = useState('');
  const [downloading, setDownloading] = useState<string | null>(null);

  const parentalEnabled = device?.parental_lock_enabled ?? false;

  const visibleCategories = useMemo(
    () => categories.filter((c) => !c.isAdult || !parentalEnabled || parentalUnlocked),
    [categories, parentalEnabled, parentalUnlocked],
  );

  const channels = useMemo(() => {
    const source = selectedCategory
      ? selectedCategory.channels
      : visibleCategories.flatMap((c) => c.channels);
    const filtered =
      parentalEnabled && !parentalUnlocked ? source.filter((c) => !c.isAdult) : source;
    const q = query.trim().toLowerCase();
    return q ? filtered.filter((c) => c.name.toLowerCase().includes(q)) : filtered;
  }, [selectedCategory, visibleCategories, query, parentalEnabled, parentalUnlocked]);

  const playChannel = async (channel: Channel, resume = false) => {
    if (channel.kind === 'series' && channel.seriesId) {
      router.push({
        pathname: '/episodes',
        params: { seriesId: channel.seriesId, title: channel.name },
      });
      return;
    }

    await addRecent(channel);
    const saved = !resume ? null : await getResume(channel.id);
    router.push({
      pathname: '/player',
      params: {
        url: channel.url,
        title: channel.name,
        isLive: channel.kind === 'live' ? '1' : '0',
        channelId: channel.id,
        resumeMs: saved ? String(saved.positionMs) : '0',
      },
    });
  };

  const handleDownload = async (channel: Channel) => {
    if (channel.kind === 'live' || !channel.url) return;
    setDownloading(channel.id);
    try {
      await downloadContent(channel.id, channel.name, channel.url);
    } finally {
      setDownloading(null);
    }
  };

  if (loadingContent) {
    return (
      <View style={styles.center}>
        <ActivityIndicator color={colors.gold} size="large" />
        <Text style={styles.dimText}>{t('loading')}</Text>
      </View>
    );
  }

  if (contentError) {
    return (
      <View style={styles.center}>
        <Text style={styles.errorText}>{contentError}</Text>
        <FoxBackButton toHome />
      </View>
    );
  }

  if (categories.length === 0) {
    return (
      <View style={styles.center}>
        <Text style={styles.dimText}>{emptyMessage ?? t('noContent')}</Text>
        <FoxBackButton toHome />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <FoxBrand height={36} />
        <View style={{ flex: 1 }} />
        <FoxBackButton toHome />
      </View>

      <TextInput
        style={styles.search}
        placeholder={`${t('search')} ${title}...`}
        placeholderTextColor={colors.textMuted}
        value={query}
        onChangeText={setQuery}
      />

      <View style={styles.body}>
        <View style={styles.sidebar}>
          <FlatList
            data={visibleCategories}
            keyExtractor={(item) => item.name}
            renderItem={({ item }) => {
              const active = selectedCategory?.name === item.name;
              return (
                <Focusable
                  style={[styles.catItem, active && styles.catItemActive]}
                  onPress={() => {
                    setSelectedCategory(item);
                    setSelectedChannel(item.channels[0] ?? null);
                  }}
                >
                  <Text style={styles.catName} numberOfLines={2}>
                    {item.name}
                  </Text>
                  <Text style={styles.catTotal}>
                    {t('total')}: {item.channels.length}
                  </Text>
                </Focusable>
              );
            }}
          />
        </View>

        {layout === 'live' ? (
          <>
            <View style={styles.listPane}>
              <FlatList
                data={channels}
                keyExtractor={(item) => item.id}
                renderItem={({ item }) => {
                  const active = selectedChannel?.id === item.id;
                  return (
                    <Focusable
                      style={[styles.liveRow, active && styles.liveRowActive]}
                      onPress={() => setSelectedChannel(item)}
                      onLongPress={() => playChannel(item)}
                    >
                      {item.logo ? (
                        <Image source={{ uri: item.logo }} style={styles.thumb} />
                      ) : (
                        <View style={styles.thumbPlaceholder} />
                      )}
                      <Text style={styles.liveName} numberOfLines={1}>
                        {item.name}
                      </Text>
                    </Focusable>
                  );
                }}
              />
            </View>
            <View style={styles.previewPane}>
              {selectedChannel?.logo ? (
                <Image source={{ uri: selectedChannel.logo }} style={styles.previewImage} resizeMode="contain" />
              ) : (
                <View style={styles.previewEmpty} />
              )}
              <Text style={styles.previewTitle} numberOfLines={2}>
                {selectedChannel?.name ?? title}
              </Text>
              {selectedChannel ? (
                <Focusable style={styles.playBtn} onPress={() => playChannel(selectedChannel)}>
                  <Text style={styles.playBtnText}>{t('play')}</Text>
                </Focusable>
              ) : null}
            </View>
          </>
        ) : (
          <View style={styles.gridPane}>
            <FlatList
              data={channels}
              numColumns={4}
              keyExtractor={(item) => item.id}
              columnWrapperStyle={styles.gridRow}
              renderItem={({ item }) => (
                <Focusable
                  style={styles.posterCard}
                  onPress={() => playChannel(item)}
                  onLongPress={() => toggleFavorite(item)}
                >
                  {item.logo ? (
                    <Image source={{ uri: item.logo }} style={styles.posterImage} />
                  ) : (
                    <View style={styles.posterPlaceholder} />
                  )}
                  <View style={styles.posterOverlay}>
                    <Text style={styles.posterTitle} numberOfLines={2}>
                      {item.name}
                    </Text>
                  </View>
                  {item.kind !== 'live' && item.url ? (
                    <Focusable
                      style={styles.downloadBtn}
                      onPress={() => handleDownload(item)}
                    >
                      <Text style={styles.downloadBtnText}>
                        {downloading === item.id ? '…' : '↓'}
                      </Text>
                    </Focusable>
                  ) : null}
                </Focusable>
              )}
            />
          </View>
        )}
      </View>

      <View style={styles.footer}>
        <MacBadge mac={device?.device_code} />
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg, padding: spacing.md },
  center: {
    flex: 1,
    backgroundColor: colors.bg,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.md,
    padding: spacing.xl,
  },
  header: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm },
  search: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    paddingVertical: 10,
    color: colors.text,
    marginBottom: spacing.sm,
    borderWidth: 1,
    borderColor: colors.border,
  },
  body: { flex: 1, flexDirection: 'row', gap: spacing.sm },
  sidebar: { width: 180 },
  catItem: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.sm,
    marginBottom: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  catItemActive: { borderColor: colors.gold, backgroundColor: 'rgba(255,140,0,0.12)' },
  catName: { color: colors.text, fontWeight: '800', fontSize: 13 },
  catTotal: { color: colors.textMuted, fontSize: 11, marginTop: 4 },
  listPane: { flex: 1 },
  liveRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.sm,
    marginBottom: spacing.xs,
    borderWidth: 2,
    borderColor: 'transparent',
    gap: spacing.sm,
  },
  liveRowActive: { borderColor: colors.gold },
  thumb: { width: 44, height: 44, borderRadius: radius.sm },
  thumbPlaceholder: { width: 44, height: 44, borderRadius: radius.sm, backgroundColor: colors.surfaceLight },
  liveName: { color: colors.text, fontWeight: '700', flex: 1, fontSize: 13 },
  previewPane: {
    width: 280,
    backgroundColor: '#000',
    borderRadius: radius.lg,
    padding: spacing.sm,
    borderWidth: 1,
    borderColor: colors.border,
  },
  previewImage: { width: '100%', height: 150, borderRadius: radius.md },
  previewEmpty: { width: '100%', height: 150, borderRadius: radius.md, backgroundColor: colors.surfaceLight },
  previewTitle: { color: colors.text, fontWeight: '800', marginTop: spacing.sm, fontSize: 14 },
  playBtn: {
    marginTop: spacing.sm,
    backgroundColor: colors.gold,
    borderRadius: radius.md,
    paddingVertical: 8,
    alignItems: 'center',
  },
  playBtnText: { color: colors.bgDeep, fontWeight: '900' },
  gridPane: { flex: 1 },
  gridRow: { gap: spacing.sm, marginBottom: spacing.sm },
  posterCard: {
    flex: 1,
    minWidth: '22%',
    maxWidth: '25%',
    aspectRatio: 0.68,
    borderRadius: radius.md,
    overflow: 'hidden',
    borderWidth: 2,
    borderColor: 'transparent',
    backgroundColor: colors.surface,
  },
  posterImage: { width: '100%', height: '100%' },
  posterPlaceholder: { flex: 1, backgroundColor: colors.surfaceLight },
  posterOverlay: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    padding: 6,
    backgroundColor: 'rgba(0,0,0,0.65)',
  },
  posterTitle: { color: colors.text, fontWeight: '800', fontSize: 11 },
  downloadBtn: {
    position: 'absolute',
    top: 6,
    right: 6,
    backgroundColor: 'rgba(0,0,0,0.7)',
    borderRadius: radius.full,
    width: 28,
    height: 28,
    alignItems: 'center',
    justifyContent: 'center',
  },
  downloadBtnText: { color: colors.gold, fontWeight: '900' },
  footer: { alignItems: 'flex-end', marginTop: spacing.sm },
  dimText: { color: colors.textMuted, textAlign: 'center' },
  errorText: { color: colors.danger, textAlign: 'center', fontWeight: '700' },
});
