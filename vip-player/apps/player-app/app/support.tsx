import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { Focusable } from '../src/components/Focusable';
import { useApp } from '../src/context/AppContext';
import { api } from '../src/lib/api';
import { SupportTicket } from '../src/lib/types';
import { colors, radius, spacing } from '../src/lib/theme';

export default function SupportScreen() {
  const { device } = useApp();
  const [subject, setSubject] = useState('');
  const [message, setMessage] = useState('');
  const [email, setEmail] = useState('');
  const [sending, setSending] = useState(false);
  const [feedback, setFeedback] = useState<string | null>(null);
  const [tickets, setTickets] = useState<SupportTicket[]>([]);

  const loadTickets = async () => {
    if (!device) return;
    try {
      const result = await api.getSupportTickets(device.device_code);
      setTickets(result.data);
    } catch {
      // Ignore: tickets list is optional.
    }
  };

  useEffect(() => {
    loadTickets();
  }, [device?.device_code]);

  const send = async () => {
    if (!subject.trim() || !message.trim() || sending) return;
    setSending(true);
    setFeedback(null);
    try {
      await api.createSupportTicket({
        device_code: device?.device_code,
        email: email.trim() || undefined,
        subject: subject.trim(),
        message: message.trim(),
      });
      setFeedback('Ticket sent! We will reply soon.');
      setSubject('');
      setMessage('');
      await loadTickets();
    } catch (e: any) {
      setFeedback(e?.message ?? 'Could not send ticket.');
    } finally {
      setSending(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={styles.scroll}>
        <Text style={styles.title}>SUPPORT</Text>

        {device?.settings?.support_message ? (
          <View style={styles.infoBox}>
            <Text style={styles.infoText}>{device.settings.support_message}</Text>
            {device.settings.support_email ? (
              <Text style={styles.infoContact}>✉️ {device.settings.support_email}</Text>
            ) : null}
            {device.settings.support_whatsapp ? (
              <Text style={styles.infoContact}>📱 {device.settings.support_whatsapp}</Text>
            ) : null}
          </View>
        ) : null}

        <Text style={styles.label}>Your email (optional)</Text>
        <TextInput
          style={styles.input}
          value={email}
          onChangeText={setEmail}
          placeholder="you@example.com"
          placeholderTextColor={colors.textMuted}
          keyboardType="email-address"
          autoCapitalize="none"
        />

        <Text style={styles.label}>Subject</Text>
        <TextInput
          style={styles.input}
          value={subject}
          onChangeText={setSubject}
          placeholder="What do you need help with?"
          placeholderTextColor={colors.textMuted}
        />

        <Text style={styles.label}>Message</Text>
        <TextInput
          style={[styles.input, styles.textarea]}
          value={message}
          onChangeText={setMessage}
          placeholder="Describe your issue…"
          placeholderTextColor={colors.textMuted}
          multiline
          numberOfLines={4}
        />

        {feedback ? <Text style={styles.feedback}>{feedback}</Text> : null}

        <Focusable style={styles.button} onPress={send}>
          {sending ? (
            <ActivityIndicator color={colors.bg} />
          ) : (
            <Text style={styles.buttonText}>Send Ticket</Text>
          )}
        </Focusable>

        {tickets.length > 0 ? (
          <View style={styles.ticketsSection}>
            <Text style={styles.sectionTitle}>Your tickets</Text>
            {tickets.map((ticket) => (
              <View key={ticket.id} style={styles.ticket}>
                <View style={styles.ticketHeader}>
                  <Text style={styles.ticketSubject} numberOfLines={1}>
                    {ticket.subject}
                  </Text>
                  <Text style={styles.ticketStatus}>{ticket.status.replace('_', ' ')}</Text>
                </View>
                <Text style={styles.ticketMessage} numberOfLines={2}>
                  {ticket.message}
                </Text>
                {ticket.admin_reply ? (
                  <View style={styles.reply}>
                    <Text style={styles.replyLabel}>Reply from support:</Text>
                    <Text style={styles.replyText}>{ticket.admin_reply}</Text>
                  </View>
                ) : null}
              </View>
            ))}
          </View>
        ) : null}
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.bg,
  },
  scroll: {
    padding: spacing.lg,
    paddingBottom: spacing.xxl,
  },
  title: {
    color: colors.text,
    fontSize: 26,
    fontWeight: '900',
    marginBottom: spacing.lg,
  },
  infoBox: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.lg,
    borderWidth: 1,
    borderColor: colors.gold,
  },
  infoText: {
    color: colors.textDim,
    fontSize: 14,
    lineHeight: 20,
  },
  infoContact: {
    color: colors.gold,
    marginTop: spacing.sm,
    fontWeight: '600',
  },
  label: {
    color: colors.textDim,
    fontSize: 13,
    fontWeight: '600',
    marginBottom: spacing.xs,
    marginTop: spacing.sm,
  },
  input: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    paddingHorizontal: spacing.md,
    paddingVertical: 10,
    color: colors.text,
    borderWidth: 1,
    borderColor: colors.border,
  },
  textarea: {
    minHeight: 100,
    textAlignVertical: 'top',
  },
  feedback: {
    color: colors.gold,
    marginTop: spacing.md,
    textAlign: 'center',
  },
  button: {
    marginTop: spacing.lg,
    backgroundColor: colors.gold,
    paddingVertical: spacing.md,
    borderRadius: radius.md,
    alignItems: 'center',
    borderWidth: 2,
    borderColor: 'transparent',
  },
  buttonText: {
    color: colors.bg,
    fontWeight: '900',
    fontSize: 16,
  },
  ticketsSection: {
    marginTop: spacing.xl,
  },
  sectionTitle: {
    color: colors.text,
    fontSize: 18,
    fontWeight: '800',
    marginBottom: spacing.md,
  },
  ticket: {
    backgroundColor: colors.surface,
    borderRadius: radius.md,
    padding: spacing.md,
    marginBottom: spacing.sm,
  },
  ticketHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  ticketSubject: {
    color: colors.text,
    fontWeight: '700',
    flex: 1,
  },
  ticketStatus: {
    color: colors.gold,
    fontSize: 11,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  ticketMessage: {
    color: colors.textMuted,
    fontSize: 13,
    marginTop: spacing.xs,
  },
  reply: {
    marginTop: spacing.sm,
    backgroundColor: colors.surfaceLight,
    borderRadius: radius.sm,
    padding: spacing.sm,
  },
  replyLabel: {
    color: colors.gold,
    fontSize: 11,
    fontWeight: '800',
  },
  replyText: {
    color: colors.textDim,
    fontSize: 13,
    marginTop: 2,
  },
});
