import { Channel, Episode } from './types';
import { api } from './api';

interface ProxyContext {
  deviceCode: string;
  playlistId: number;
}

export async function loadXtreamLive(context: ProxyContext): Promise<Channel[]> {
  const result = await api.getPlaylistLive(context.deviceCode, context.playlistId);
  return result.data.channels;
}

export async function loadXtreamMovies(context: ProxyContext): Promise<Channel[]> {
  const result = await api.getPlaylistVod(context.deviceCode, context.playlistId);
  return result.data.channels;
}

export async function loadXtreamSeries(context: ProxyContext): Promise<Channel[]> {
  const result = await api.getPlaylistSeries(context.deviceCode, context.playlistId);
  return result.data.channels;
}

export async function loadXtreamEpisodes(
  context: ProxyContext,
  seriesId: string,
): Promise<Episode[]> {
  const result = await api.getPlaylistEpisodes(context.deviceCode, context.playlistId, seriesId);
  return result.data.episodes;
}

export async function loadXtream(context: ProxyContext): Promise<Channel[]> {
  const [live, movies, series] = await Promise.all([
    loadXtreamLive(context),
    loadXtreamMovies(context),
    loadXtreamSeries(context),
  ]);
  return [...live, ...movies, ...series];
}
