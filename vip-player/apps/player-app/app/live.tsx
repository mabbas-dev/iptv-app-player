import React from 'react';
import { View } from 'react-native';
import { CategoryBrowser } from '../src/components/CategoryBrowser';
import { useApp } from '../src/context/AppContext';
import { colors } from '../src/lib/theme';

export default function LiveScreen() {
  const { content } = useApp();

  return (
    <View style={{ flex: 1, backgroundColor: colors.bg }}>
      <CategoryBrowser
        title="LIVE TV BOX"
        categories={content?.live ?? []}
        emptyMessage="No live channels found in the active playlist."
      />
    </View>
  );
}
