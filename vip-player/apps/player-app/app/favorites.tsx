import { useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import { FlatList, StyleSheet, Text, View } from 'react-native';
import { FoxBackButton } from '../src/components/FoxBackButton';
import { FoxBrand } from '../src/components/FoxBrand';
import { Focusable } from '../src/components/Focusable';
import { getFavorites } from '../src/lib/storage';
import { Channel } from '../src/lib/types';
import { colors, radius, spacing } from '../src/lib/theme';
import { t } from '../src/lib/i18n';

export default function FavoritesScreen() {
  const router = useRouter();
  const [items, setItems] = useState<Channel[]>([]);

  useEffect(() => {
    getFavorites().then(setItems);
  }, []);

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <FoxBrand height={36} />
        <View style={{ flex: 1 }} />
        <FoxBackButton toHome />
      </View>
      <Text style={styles.title}>{t('favourites')}</Text>
      <FlatList
        data={items}
        keyExtractor={(item) => item.id}
        renderItem={({ item }) => (
          <Focusable
            style={styles.row}
            onPress={() =>
              router.push({
                pathname: '/player',
                params: {
                  url: item.url,
                  title: item.name,
                  isLive: item.kind === 'live' ? '1' : '0',
                  channelId: item.id,
                },
              })
            }
          >
            <Text style={styles.name}>{item.name}</Text>
            <Text style={styles.group}>{item.group}</Text>
          </Focusable>
        )}
        ListEmptyComponent={<Text style={styles.empty}>{t('noContent')}</Text>}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.bg, padding: spacing.md },
  header: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm },
  title: { color: colors.text, fontWeight: '900', fontSize: 22, marginBottom: spacing.md },
  row: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderWidth: 2,
    borderColor: 'transparent',
  },
  name: { color: colors.text, fontWeight: '800' },
  group: { color: colors.textMuted, fontSize: 12, marginTop: 4 },
  empty: { color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
});
