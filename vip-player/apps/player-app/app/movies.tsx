import React from 'react';
import { FoxContentBrowser } from '../src/components/FoxContentBrowser';
import { useApp } from '../src/context/AppContext';
import { t } from '../src/lib/i18n';

export default function MoviesScreen() {
  const { content } = useApp();

  return (
    <FoxContentBrowser
      title={t('movies')}
      categories={content?.movies ?? []}
      layout="grid"
      emptyMessage={t('noContent')}
    />
  );
}
