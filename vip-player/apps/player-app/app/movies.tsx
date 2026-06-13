import React from 'react';
import { View } from 'react-native';
import { CategoryBrowser } from '../src/components/CategoryBrowser';
import { useApp } from '../src/context/AppContext';
import { colors } from '../src/lib/theme';

export default function MoviesScreen() {
  const { content } = useApp();

  return (
    <View style={{ flex: 1, backgroundColor: colors.bg }}>
      <CategoryBrowser
        title="MOVIES"
        categories={content?.movies ?? []}
        emptyMessage="No movies found in the active playlist."
      />
    </View>
  );
}
