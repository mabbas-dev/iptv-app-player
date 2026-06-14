import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Linking,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import QRCode from 'react-native-qrcode-svg';
import { Focusable } from '../src/components/Focusable';
import { useApp } from '../src/context/AppContext';
import { friendlyPlaylistError } from '../src/lib/errors';
import { colors, radius, spacing } from '../src/lib/theme';

export default function ActivationScreen() {
  const router = useRouter();
  const { playlistError } = useLocalSearchParams<{ playlistError?: string }>();
  const { device, refreshDevice, loadPlaylists } = useApp();
  const [checking, setChecking] = useState(false);
  const [localError, setLocalError] = useState<string | null>(null);

  useEffect(() => {
    const interval = setInterval(() => {
      refreshDevice().catch(() => {});
    }, 10000);
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    if (playlistError) {
      setLocalError(String(playlistError));
    }
  }, [playlistError]);

  const openApp = async () => {
    setChecking(true);
    setLocalError(null);
    try {
      const updated = await refreshDevice();
      if (!updated?.is_watchable) {
        setLocalError('Your trial or subscription is not active. Renew to continue.');
        return;
      }
      if ((updated.playlists_count ?? 0) === 0) {
        setLocalError('No playlist yet. Scan the QR code and upload your IPTV playlist.');
        return;
      }
      await loadPlaylists(true);
      router.replace('/home');
    } catch (e: any) {
      setLocalError(friendlyPlaylistError(e));
    } finally {
      setChecking(false);
    }
  };

  const uploadUrl = device?.upload_url ?? '';
  const statusLabel = device?.status?.toUpperCase() ?? '…';
  const hasPlaylist = (device?.playlists_count ?? 0) > 0;

  return (
    <View style={styles.container}>
      <View style={styles.row}>
        <View style={styles.left}>
          <Text style={styles.heading}>Upload Playlist</Text>
          <Text style={styles.sub}>
            Add your IPTV playlist on our website using your Device ID below.
          </Text>

          <View style={styles.codeCard}>
            <Text style={styles.codeLabel}>DEVICE ID</Text>
            <Text style={styles.code}>{device?.device_code ?? '··:··:··:··:··:··'}</Text>
            <View style={[styles.statusBadge, statusStyle(device?.status)]}>
              <Text style={styles.statusText}>{statusLabel}</Text>
            </View>
          </View>

          {device?.status === 'expired' || device?.status === 'blocked' ? (
            <View style={styles.errorBox}>
              <Text style={styles.errorText}>
                Your trial or subscription has expired. Renew to continue.
              </Text>
              <Focusable
                style={styles.renewBtn}
                onPress={() => Linking.openURL(device.activation_url)}
              >
                <Text style={styles.renewBtnText}>Renew</Text>
              </Focusable>
            </View>
          ) : localError ? (
            <View style={styles.errorBox}>
              <Text style={styles.errorText}>{localError}</Text>
              <Text style={styles.errorHint}>
                {localError.includes('expired') || localError.includes('subscription')
                  ? 'Tap Renew or contact your reseller.'
                  : localError.includes('server') || localError.includes('reach')
                    ? 'Your IPTV server may be slow. Tap Continue to try again.'
                    : 'Check playlist on our website, then tap Continue.'}
              </Text>
            </View>
          ) : (
            <Text style={styles.hint}>
              {hasPlaylist
                ? 'Playlist found. Tap Continue to sync and open the app.'
                : 'Waiting for playlist upload — this screen stays until you add one.'}
            </Text>
          )}
        </View>

        <View style={styles.right}>
          {uploadUrl ? (
            <View style={styles.qrWrap}>
              <QRCode value={uploadUrl} size={96} backgroundColor={colors.surface} color={colors.gold} />
              <Text style={styles.qrHint}>Scan to upload playlist</Text>
              <Text style={styles.qrUrl} numberOfLines={1}>
                {uploadUrl.replace(/^https?:\/\//, '')}
              </Text>
            </View>
          ) : null}

          <View style={styles.actions}>
            <Focusable style={styles.button} onPress={openApp} hasTVPreferredFocus>
              {checking ? (
                <ActivityIndicator color={colors.bg} />
              ) : (
                <Text style={styles.buttonText}>{hasPlaylist ? 'Continue' : 'Check Status'}</Text>
              )}
            </Focusable>
            <Focusable
              style={styles.buttonGhost}
              onPress={() => uploadUrl && Linking.openURL(uploadUrl)}
            >
              <Text style={styles.buttonGhostText}>Open Upload Page</Text>
            </Focusable>
            <Focusable style={styles.buttonGhost} onPress={() => router.push('/support')}>
              <Text style={styles.buttonGhostText}>Support</Text>
            </Focusable>
          </View>
        </View>
      </View>
    </View>
  );
}

function statusStyle(status?: string) {
  switch (status) {
    case 'active':
    case 'trial':
      return { backgroundColor: colors.success };
    case 'blocked':
    case 'expired':
      return { backgroundColor: colors.danger };
    default:
      return { backgroundColor: colors.surfaceLight };
  }
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    justifyContent: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.lg,
    maxWidth: 720,
    alignSelf: 'center',
    width: '100%',
  },
  left: { flex: 1, gap: spacing.sm },
  right: { width: 200, alignItems: 'center', gap: spacing.md },
  heading: { color: colors.text, fontSize: 18, fontWeight: '900' },
  sub: { color: colors.textMuted, fontSize: 11, lineHeight: 16 },
  codeCard: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.md,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.border,
  },
  codeLabel: { color: colors.textMuted, fontSize: 9, letterSpacing: 2 },
  code: {
    color: colors.gold,
    fontSize: 15,
    fontWeight: '800',
    letterSpacing: 1,
    marginTop: 2,
    fontVariant: ['tabular-nums'],
  },
  statusBadge: { marginTop: 6, borderRadius: 999, paddingHorizontal: 10, paddingVertical: 2 },
  statusText: { color: colors.bg, fontWeight: '800', fontSize: 9 },
  qrWrap: { alignItems: 'center', gap: 4 },
  qrHint: { color: colors.textMuted, fontSize: 10, textAlign: 'center' },
  qrUrl: { color: colors.textMuted, fontSize: 8, maxWidth: 180, textAlign: 'center' },
  errorBox: {
    backgroundColor: 'rgba(239,68,68,0.12)',
    borderRadius: radius.md,
    padding: spacing.sm,
    gap: 4,
  },
  errorText: { color: colors.danger, fontSize: 11, lineHeight: 15 },
  errorHint: { color: colors.textMuted, fontSize: 10 },
  hint: { color: colors.textMuted, fontSize: 11, lineHeight: 15 },
  actions: { gap: spacing.xs, width: '100%' },
  button: {
    backgroundColor: colors.gold,
    paddingVertical: 8,
    borderRadius: radius.md,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  buttonText: { color: colors.bg, fontWeight: '900', fontSize: 12 },
  buttonGhost: {
    backgroundColor: colors.surface,
    paddingVertical: 7,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: 'center',
  },
  buttonGhostText: { color: colors.text, fontWeight: '700', fontSize: 11 },
  renewBtn: {
    alignSelf: 'flex-start',
    backgroundColor: colors.gold,
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    borderRadius: radius.md,
  },
  renewBtnText: { color: colors.bg, fontWeight: '900', fontSize: 11 },
});
