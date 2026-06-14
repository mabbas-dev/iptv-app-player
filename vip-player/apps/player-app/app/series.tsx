import React from 'react';
import { FoxContentBrowser } from '../src/components/FoxContentBrowser';
import { useApp } from '../src/context/AppContext';
import { t } from '../src/lib/i18n';

export default function SeriesScreen() {
  const { content } = useApp();

  return (
    <FoxContentBrowser
      title={t('series')}
      categories={content?.series ?? []}
      layout="grid"
      emptyMessage={t('noContent')}
    />
  );
}
