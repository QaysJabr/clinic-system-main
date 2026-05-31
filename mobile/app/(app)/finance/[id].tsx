import { useEffect, useMemo, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { useLocalSearchParams } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchInvoice, recordPayment } from '@/api/services/invoices.service';
import { canRecordPayment } from '@/auth/permissions';
import { useAuth } from '@/auth/AuthContext';
import { FormSection } from '@/components/layout/FormSection';
import {
  FormScreen,
  FormScreenError,
  FormScreenLoading,
} from '@/components/layout/FormScreen';
import { DateField } from '@/components/ui/DateTimeField';
import { ChipGroup } from '@/components/ui/ChipGroup';
import { Badge, Button, Card, Input } from '@/components/ui/primitives';
import { useSemanticColors } from '@/hooks/useSemanticColors';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError, invoiceStatusLabels } from '@/utils/format';
import { fireHapticSuccess } from '@/utils/haptics';
import type { RecordPaymentPayload } from '@/types/api';

const paymentMethods = ['cash', 'card', 'bank_transfer', 'other'] as const;
const paymentLabels: Record<(typeof paymentMethods)[number], string> = {
  cash: 'نقدي',
  card: 'بطاقة',
  bank_transfer: 'تحويل',
  other: 'أخرى',
};

function todayIsoDate(): string {
  const now = new Date();
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
}

export default function InvoiceDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const invoiceId = Number(id);
  const { user } = useAuth();
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const colors = useSemanticColors();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const query = useQuery({
    queryKey: queryKeys.invoice(invoiceId),
    queryFn: () => fetchInvoice(invoiceId),
    enabled: Number.isFinite(invoiceId) && invoiceId > 0,
  });

  const [amount, setAmount] = useState('');
  const [method, setMethod] = useState<RecordPaymentPayload['payment_method']>('cash');
  const [paymentDate, setPaymentDate] = useState(todayIsoDate());
  const [notes, setNotes] = useState('');

  useEffect(() => {
    if (query.data?.remaining) {
      setAmount(query.data.remaining);
    }
  }, [query.data?.remaining]);

  const paymentMutation = useMutation({
    mutationFn: () =>
      recordPayment({
        invoice_id: invoiceId,
        amount: Number(amount),
        payment_method: method,
        payment_date: paymentDate,
        notes: notes.trim() || null,
      }),
    onSuccess: async () => {
      toast.showSuccess('تم تسجيل الدفعة');
      fireHapticSuccess();
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.invoice(invoiceId) }),
        queryClient.invalidateQueries({ queryKey: ['invoices'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
      ]);
      setNotes('');
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  if (query.isLoading) {
    return <FormScreenLoading title="تفاصيل الفاتورة" />;
  }

  if (query.isError || !query.data) {
    return (
      <FormScreenError
        title="تفاصيل الفاتورة"
        message={formatApiError(query.error)}
        onRetry={() => void query.refetch()}
      />
    );
  }

  const invoice = query.data;
  const canPay = canRecordPayment(user) && Number(invoice.remaining) > 0;

  return (
    <FormScreen title={invoice.invoice_number} subtitle="تفاصيل الفاتورة">
      <Card>
        <View style={styles.header}>
          <Badge
            label={invoiceStatusLabels[invoice.status] ?? invoice.status}
            color={colors.invoice[invoice.status] ?? theme.colors.brand}
          />
        </View>
        <Text style={styles.label}>المريض</Text>
        <Text style={styles.value}>{invoice.patient?.full_name ?? `#${invoice.patient_id}`}</Text>
        {invoice.doctor?.full_name ? (
          <>
            <Text style={styles.label}>الطبيب</Text>
            <Text style={styles.value}>{invoice.doctor.full_name}</Text>
          </>
        ) : null}
        <View style={styles.amounts}>
          <Text style={styles.meta}>الإجمالي: {invoice.total}</Text>
          <Text style={styles.meta}>المدفوع: {invoice.paid}</Text>
          <Text style={styles.remaining}>المتبقّي: {invoice.remaining}</Text>
        </View>
      </Card>

      {canPay ? (
        <FormSection title="تسجيل دفعة" subtitle="سريع — للمحاسب والاستقبال">
          <Input label="المبلغ" value={amount} onChangeText={setAmount} keyboardType="decimal-pad" />
          <ChipGroup
            options={[...paymentMethods]}
            value={method}
            onChange={(value) => setMethod(value as RecordPaymentPayload['payment_method'])}
            labels={paymentLabels}
          />
          <DateField label="تاريخ الدفع" value={paymentDate} onChange={setPaymentDate} />
          <Input label="ملاحظات" value={notes} onChangeText={setNotes} placeholder="اختياري" />
          <Button
            label="تسجيل الدفعة"
            icon="check"
            onPress={() => paymentMutation.mutate()}
            loading={paymentMutation.isPending}
            disabled={!amount.trim() || paymentMutation.isPending}
          />
        </FormSection>
      ) : null}
    </FormScreen>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    header: { flexDirection: 'row-reverse', marginBottom: theme.spacing.md },
    label: { ...theme.typography.caption, color: theme.colors.textMuted, textAlign: 'right' },
    value: { ...theme.typography.subtitle, color: theme.colors.text, textAlign: 'right' },
    amounts: { marginTop: theme.spacing.md, alignItems: 'flex-end', gap: 4 },
    meta: { ...theme.typography.body, color: theme.colors.textMuted },
    remaining: { ...theme.typography.subtitle, color: theme.colors.brand, fontWeight: '700' },
  });
}
