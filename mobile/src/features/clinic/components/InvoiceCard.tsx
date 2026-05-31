import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Badge, Card } from '@/components/ui/primitives';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useSemanticColors } from '@/hooks/useSemanticColors';
import type { Invoice } from '@/types/api';
import { invoiceStatusLabels } from '@/utils/format';

export function InvoiceCard({ invoice, onPress }: { invoice: Invoice; onPress?: () => void }) {
  const { theme } = useAppTheme();
  const colors = useSemanticColors();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const statusColor = colors.invoice[invoice.status] ?? theme.colors.brand;

  const content = (
    <>
      <View style={styles.header}>
        <Badge label={invoiceStatusLabels[invoice.status] ?? invoice.status} color={statusColor} />
        <Text style={styles.number}>{invoice.invoice_number}</Text>
      </View>
      <Text style={styles.patient}>{invoice.patient?.full_name ?? `#${invoice.patient_id}`}</Text>
      <View style={styles.amounts}>
        <Text style={styles.total}>الإجمالي: {invoice.total}</Text>
        <Text style={styles.remaining}>متبقّي: {invoice.remaining}</Text>
      </View>
    </>
  );

  if (onPress) {
    return (
      <Pressable onPress={onPress} style={({ pressed }) => [pressed && styles.pressed]}>
        <Card>{content}</Card>
      </Pressable>
    );
  }

  return <Card>{content}</Card>;
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    header: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: theme.spacing.sm,
    },
    number: { ...theme.typography.caption, color: theme.colors.textMuted },
    patient: { ...theme.typography.subtitle, color: theme.colors.text, textAlign: 'right' },
    amounts: { marginTop: theme.spacing.sm, alignItems: 'flex-end', gap: 2 },
    total: { ...theme.typography.caption, color: theme.colors.textMuted },
    remaining: { ...theme.typography.body, color: theme.colors.brand, fontWeight: '700' },
    pressed: { opacity: 0.92 },
  });
}
