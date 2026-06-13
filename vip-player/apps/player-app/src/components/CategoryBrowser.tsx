import { useRouter } from 'expo-router';
import React, { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useApp } from '../context/AppContext';
import { addRecent, toggleFavorite } from '../lib/storage';
import { Category, Channel } from '../lib/types';
import { colors, radius, spacing } from '../lib/theme';
import { ChannelItem } from './ChannelItem';
import { Focusable } from './Focusable';

interface CategoryBrowserProps {
  title: string;
  categories: Category[];
  emptyMessage?: string;
}

export function CategoryBrowser({ title, categories, emptyMessage }: CategoryBrowserProps) {
  const router = useRouter();
  const insets = useSafeAreaInsets();
  const { device, parentalUnlocked, loadingContent, contentError } = useApp();
  const [selectedCategory, setSelectedCategory] = useState<Category | null>(null);
  const [query, setQuery] = useState('');

  const parentalEnabled = device?.parental_lock_enabled ?? false;

  const visibleCategories = useMemo(
    () => categories.filter((c) => !c.isAdult || !parentalEnabled || parentalUnlocked),
    [categories, parentalEnabled, parentalUnlocked],
  );

  const lockedCategories = useMemo(
    () => categories.filter((c) => c.isAdult && parentalEnabled && !parentalUnlocked),
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

  const playChannel = async (channel: Channel) => {
    if (channel.kind === 'series' && channel.seriesId) {
      router.push({
        pathname: '/episodes',
        params: { seriesId: channel.seriesId, title: channel.name },
      });
      return;
    }

    await addRecent(channel);
    router.push({
      pathname: '/player',
      params: {
        url: channel.url,
        title: channel.name,
        isLive: channel.kind === 'live' ? '1' : '0',
      },
    });
  };

  if (loadingContent) {
    return (
      <View style={styles.center}>
        <ActivityIndicator color={colors.gold} size="large" />
        <Text style={styles.dimText}>Syncing playlist…</Text>
      </View>
    );
  }

  if (contentError) {
    return (
      <View style={styles.center}>
        <Text style={styles.errorText}>{contentError}</Text>
      </View>
    );
  }

  if (categories.length === 0) {
    return (
      <View style={styles.center}>
        <Text style={styles.dimText}>
          {emptyMessage ?? 'No content. Upload your playlist on our website.'}
        </Text>
      </View>
    );
  }

  return (
    <View style={[styles.container, { paddingTop: insets.top + spacing.sm }]}>
      <View style={styles.headerRow}>
        <Focusable style={styles.backButton} onPress={() => router.replace('/home')}>
          <Text style={styles.backText}>‹ Home</Text>
        </Focusable>
        <Text style={styles.title}>{title}</Text>
      </View>

      <TextInput
        style={styles.search}
        placeholder={`Search ${title.toLowerCase()}…`}
        placeholderTextColor={colors.textMuted}
        value={query}
        onChangeText={setQuery}
      />

      <FlatList
        horizontal
        showsHorizontalScrollIndicator={false}
        data={[null, ...visibleCategories, ...lockedCategories] as (Category | null)[]}
        keyExtractor={(item) => (item ? item.name : '__all__')}
        style={styles.categoryList}
        contentContainerStyle={styles.categoryListContent}
        renderItem={({ item }) => {
          const isLocked = item?.isAdult && parentalEnabled && !parentalUnlocked;
          const isSelected = item === selectedCategory || (!item && !selectedCategory);
          return (
            <Focusable
              style={[styles.categoryChip, isSelected && styles.categoryChipActive]}
              onPress={() => {
                if (isLocked) {
                  router.push({ pathname: '/parental', params: { mode: 'verify' } });
                  return;
                }
                setSelectedCategory(item);
              }}
            >
              <Text
                style={[styles.categoryText, isSelected && styles.categoryTextActive]}
                numberOfLines={1}
              >
                {isLocked ? '🔒 ' : ''}
                {item ? item.name : 'All'}
              </Text>
            </Focusable>
          );
        }}
      />

      <FlatList
        style={styles.channelList}
        contentContainerStyle={styles.channelListContent}
        data={channels}
        keyExtractor={(item) => item.id}
        renderItem={({ item }) => (
          <ChannelItem
            channel={item}
            onPress={() => playChannel(item)}
            onLongPress={() => toggleFavorite(item)}
          />
        )}
        initialNumToRender={20}
        ListEmptyComponent={
          <Text style={[styles.dimText, { marginTop: spacing.xl }]}>No results.</Text>
        }
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    paddingHorizontal: spacing.md,
    backgroundColor: colors.bg,
  },
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: spacing.md,
    padding: spacing.xl,
    backgroundColor: colors.bg,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
    marginBottom: spacing.sm,
  },
  backButton: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    borderWidth: 2,
    borderColor: colors.border,
  },
  backText: {
    color: colors.text,
    fontWeight: '800',
    fontSize: 14,
  },
  title: {
    color: colors.text,
    fontSize: 22,
    fontWeight: '900',
    flex: 1,
  },
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
  categoryList: {
    flexGrow: 0,
    marginBottom: spacing.sm,
    maxHeight: 44,
  },
  categoryListContent: {
    alignItems: 'center',
    paddingVertical: 2,
  },
  categoryChip: {
    backgroundColor: colors.surface,
    borderRadius: 999,
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    marginRight: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
    height: 36,
    justifyContent: 'center',
    maxWidth: 200,
  },
  categoryChipActive: {
    backgroundColor: colors.gold,
  },
  categoryText: {
    color: colors.textDim,
    fontWeight: '600',
    fontSize: 13,
    lineHeight: 18,
  },
  categoryTextActive: {
    color: colors.bg,
  },
  channelList: {
    flex: 1,
  },
  channelListContent: {
    paddingBottom: spacing.xl,
  },
  dimText: {
    color: colors.textMuted,
    textAlign: 'center',
  },
  errorText: {
    color: colors.danger,
    textAlign: 'center',
  },
});
