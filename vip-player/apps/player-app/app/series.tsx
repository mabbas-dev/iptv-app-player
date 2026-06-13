import React from 'react';
import { View } from 'react-native';
import { CategoryBrowser } from '../src/components/CategoryBrowser';
import { useApp } from '../src/context/AppContext';
import { colors } from '../src/lib/theme';

export default function SeriesScreen() {
  const { content } = useApp();

  return (
    <View style={{ flex: 1, backgroundColor: colors.bg }}>
      <CategoryBrowser
        title="SERIES"
        categories={content?.series ?? []}
        emptyMessage="No series found in the active playlist."
      />
    </View>
  );
}
