export type SyncSection = 'live' | 'vod' | 'series' | 'guide';

export type SyncSectionStatus = 'waiting' | 'loading' | 'done' | 'error';

export interface SyncProgress {
  live: SyncSectionStatus;
  vod: SyncSectionStatus;
  series: SyncSectionStatus;
  guide: SyncSectionStatus;
  message: string;
}

export const INITIAL_SYNC_PROGRESS: SyncProgress = {
  live: 'waiting',
  vod: 'waiting',
  series: 'waiting',
  guide: 'waiting',
  message: 'Preparing sync…',
};

export type SyncProgressCallback = (progress: Partial<SyncProgress>) => void;
