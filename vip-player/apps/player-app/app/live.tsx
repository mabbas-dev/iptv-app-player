import React from 'react';
import { FoxContentBrowser } from '../src/components/FoxContentBrowser';
import { useApp } from '../src/context/AppContext';
import { t } from '../src/lib/i18n';

export default function LiveScreen() {
  const { content } = useApp();

  return (
    <FoxContentBrowser
      title={t('liveTv')}
      categories={content?.live ?? []}
      layout="live"
      emptyMessage={t('noContent')}
    />
  );
}
