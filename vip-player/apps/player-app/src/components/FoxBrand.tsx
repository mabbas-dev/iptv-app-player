import React from 'react';
import { Image, StyleSheet, View } from 'react-native';

interface FoxBrandProps {
  height?: number;
  centered?: boolean;
}

export function FoxBrand({ height = 48, centered = false }: FoxBrandProps) {
  return (
    <View style={[styles.wrap, centered && styles.centered]}>
      <Image
        source={require('../../assets/fox-brand.png')}
        style={{ height, width: height * 3.2 }}
        resizeMode="contain"
      />
    </View>
  );
}

const styles = StyleSheet.create({
  wrap: { flexDirection: 'row', alignItems: 'center' },
  centered: { alignSelf: 'center' },
});
