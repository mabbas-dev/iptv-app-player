import { useLocalSearchParams, useRouter } from 'expo-router';
import React, { useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { useApp } from '../src/context/AppContext';
import { api } from '../src/lib/api';
import { colors, radius, spacing } from '../src/lib/theme';

const KEYS = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'C', '0', '⌫'];

export default function ParentalScreen() {
  const router = useRouter();
  const { mode } = useLocalSearchParams<{ mode?: string }>();
  const { device, setParentalUnlocked, refreshDevice, setDevice } = useApp();

  const isManage = mode === 'manage';
  const isMacLock = mode === 'maclock';
  const [pin, setPin] = useState('');
  const [currentPin, setCurrentPin] = useState<string | null>(null);
  const [stage, setStage] = useState<'verify' | 'new'>(
    isManage && device?.has_parental_pin ? 'verify' : isManage ? 'new' : 'verify',
  );
  const [message, setMessage] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  const heading = isMacLock
    ? device?.mac_locked
      ? 'Unlock MAC'
      : 'Lock MAC'
    : !isManage
      ? 'Enter PIN'
      : stage === 'verify'
        ? 'Current PIN'
        : 'New PIN';

  const pressKey = (key: string) => {
    setMessage(null);
    if (key === 'C') setPin('');
    else if (key === '⌫') setPin((p) => p.slice(0, -1));
    else if (pin.length < 4) setPin((p) => p + key);
  };

  const submit = async () => {
    if (!device || pin.length !== 4 || busy) return;
    setBusy(true);

    try {
      if (isMacLock) {
        const locked = !device.mac_locked;
        await api.setMacLock(device.device_code, locked, pin);
        const updated = await refreshDevice();
        if (updated) setDevice(updated);
        setMessage(locked ? 'MAC locked.' : 'MAC unlocked.');
        setTimeout(() => router.back(), 800);
      } else if (!isManage) {
        await api.verifyParentalPin(device.device_code, pin);
        setParentalUnlocked(true);
        router.back();
      } else if (stage === 'verify') {
        await api.verifyParentalPin(device.device_code, pin);
        setCurrentPin(pin);
        setPin('');
        setStage('new');
      } else {
        await api.setParentalLock(device.device_code, true, pin, currentPin ?? undefined);
        const updated = await refreshDevice();
        if (updated) setDevice(updated);
        setMessage('PIN saved.');
        setTimeout(() => router.back(), 800);
      }
    } catch (e: any) {
      setMessage(e?.message ?? 'Incorrect PIN.');
      setPin('');
    } finally {
      setBusy(false);
    }
  };

  const disableLock = async () => {
    if (!device || busy) return;
    if (device.has_parental_pin && pin.length !== 4) {
      setMessage('Enter PIN first.');
      return;
    }
    setBusy(true);
    try {
      await api.setParentalLock(device.device_code, false, undefined, pin || undefined);
      const updated = await refreshDevice();
      if (updated) setDevice(updated);
      setParentalUnlocked(false);
      router.back();
    } catch (e: any) {
      setMessage(e?.message ?? 'Could not disable lock.');
    } finally {
      setBusy(false);
    }
  };

  const submitLabel = isMacLock
    ? device?.mac_locked
      ? 'Unlock'
      : 'Lock'
    : !isManage
      ? 'Unlock'
      : stage === 'verify'
        ? 'Continue'
        : 'Save';

  return (
    <View style={styles.container}>
      <View style={styles.main}>
        <Text style={styles.lockIcon}>{isMacLock ? '🔐' : '🔒'}</Text>
        <Text style={styles.heading}>{heading}</Text>

        <View style={styles.dots}>
          {[0, 1, 2, 3].map((i) => (
            <View key={i} style={[styles.dot, i < pin.length && styles.dotFilled]} />
          ))}
        </View>

        {message ? <Text style={styles.message}>{message}</Text> : null}

        <View style={styles.pad}>
          {KEYS.map((key, index) => (
            <Focusable
              key={key}
              style={styles.key}
              onPress={() => pressKey(key)}
              hasTVPreferredFocus={index === 0}
            >
              <Text style={styles.keyText}>{key}</Text>
            </Focusable>
          ))}
        </View>
      </View>

      <View style={styles.actions}>
        <Focusable style={styles.submit} onPress={submit}>
          <Text style={styles.submitText}>{submitLabel}</Text>
        </Focusable>
        {isManage && device?.parental_lock_enabled ? (
          <Focusable style={styles.disable} onPress={disableLock}>
            <Text style={styles.disableText}>Disable</Text>
          </Focusable>
        ) : null}
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    justifyContent: 'space-between',
  },
  main: { alignItems: 'center', flex: 1, justifyContent: 'center' },
  lockIcon: { fontSize: 22 },
  heading: { color: colors.text, fontSize: 14, fontWeight: '800', marginTop: 6, textAlign: 'center' },
  dots: { flexDirection: 'row', gap: 8, marginTop: 10 },
  dot: { width: 10, height: 10, borderRadius: 5, backgroundColor: colors.surfaceLight },
  dotFilled: { backgroundColor: colors.gold },
  message: { color: colors.danger, marginTop: 6, fontSize: 11, textAlign: 'center' },
  pad: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    width: 204,
    gap: 6,
    marginTop: 10,
    justifyContent: 'center',
  },
  key: {
    width: 60,
    height: 38,
    backgroundColor: colors.surface,
    borderRadius: radius.sm,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  keyText: { color: colors.text, fontSize: 16, fontWeight: '700' },
  actions: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    gap: spacing.sm,
    paddingBottom: spacing.xs,
  },
  submit: {
    backgroundColor: colors.gold,
    paddingHorizontal: spacing.xl,
    paddingVertical: 8,
    borderRadius: radius.md,
    borderWidth: 2,
    borderColor: 'transparent',
    minWidth: 120,
    alignItems: 'center',
  },
  submitText: { color: colors.bg, fontWeight: '900', fontSize: 13 },
  disable: {
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: colors.danger,
    alignItems: 'center',
  },
  disableText: { color: colors.danger, fontWeight: '700', fontSize: 12 },
});
