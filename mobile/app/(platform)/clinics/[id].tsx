import { useEffect, useMemo, useState } from 'react';
import { Alert, ScrollView, StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useLocalSearchParams } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  activatePlatformClinic,
  exportPlatformPaymentsCsv,
  fetchPlatformClinic,
  recordPlatformClinicPayment,
  suspendPlatformClinic,
  updatePlatformClinic,
} from '@/api/services/platform.service';
import { ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { FormSection } from '@/components/layout/FormSection';
import { ScreenHeader } from '@/components/layout/ScreenHeader';
import { DateRangeFilter, DateTimeField } from '@/features/platform/components/DateRangeFilter';
import { PaymentSourceBadge } from '@/features/platform/components/PaymentSourceBadge';
import { formatPlatformDateTime } from '@/features/platform/utils/platform-format';
import { Badge, Button, Card, Input } from '@/components/ui/primitives';
import { ChipGroup } from '@/components/ui/ChipGroup';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';

function todayIsoDate(): string {
  const now = new Date();
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
}

export default function PlatformClinicDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const clinicId = Number(id);
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const [paidFrom, setPaidFrom] = useState('');
  const [paidTo, setPaidTo] = useState('');
  const [amount, setAmount] = useState('');
  const [notes, setNotes] = useState('');
  const [paidAt, setPaidAt] = useState('');
  const [paymentExpiresAt, setPaymentExpiresAt] = useState('');
  const [subscriptionExpiresAt, setSubscriptionExpiresAt] = useState('');
  const [subscriptionStatus, setSubscriptionStatus] = useState<'active' | 'expired'>('active');
  const [exportingPayments, setExportingPayments] = useState(false);

  const paymentFilters = useMemo(
    () => ({
      paid_from: paidFrom.trim() || undefined,
      paid_to: paidTo.trim() || undefined,
    }),
    [paidFrom, paidTo],
  );

  const query = useQuery({
    queryKey: queryKeys.platformClinic(clinicId, paymentFilters),
    queryFn: () => fetchPlatformClinic(clinicId, paymentFilters),
    enabled: Number.isFinite(clinicId) && clinicId > 0,
  });

  useEffect(() => {
    if (!query.data?.clinic) {
      return;
    }
    const clinicData = query.data.clinic;
    setSubscriptionExpiresAt(clinicData.subscription_expires_at ?? '');
    setSubscriptionStatus(clinicData.subscription_status === 'expired' ? 'expired' : 'active');
  }, [query.data?.clinic]);

  useEffect(() => {
    if (!paidAt) {
      setPaidAt(`${todayIsoDate()}T09:00:00`);
    }
  }, [paidAt]);

  const invalidate = async () => {
    await Promise.all([
      queryClient.invalidateQueries({ queryKey: ['platform', 'clinics', clinicId] }),
      queryClient.invalidateQueries({ queryKey: ['platform', 'clinics'] }),
      queryClient.invalidateQueries({ queryKey: queryKeys.platformDashboard }),
    ]);
  };

  const activateMutation = useMutation({
    mutationFn: () => activatePlatformClinic(clinicId),
    onSuccess: async () => {
      toast.showSuccess('تم تفعيل العيادة');
      await invalidate();
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  const suspendMutation = useMutation({
    mutationFn: () => suspendPlatformClinic(clinicId),
    onSuccess: async () => {
      toast.showSuccess('تم تعليق العيادة');
      await invalidate();
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  const paymentMutation = useMutation({
    mutationFn: () =>
      recordPlatformClinicPayment(clinicId, {
        amount: Number(amount),
        paid_at: paidAt.trim() || undefined,
        notes: notes.trim() || null,
        subscription_expires_at: paymentExpiresAt.trim() || null,
      }),
    onSuccess: async () => {
      toast.showSuccess('تم تسجيل الدفعة');
      setAmount('');
      setNotes('');
      setPaymentExpiresAt('');
      await invalidate();
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  const updateMutation = useMutation({
    mutationFn: (planId: number | null) =>
      updatePlatformClinic(clinicId, {
        is_active: query.data?.clinic.is_active ?? true,
        subscription_status: subscriptionStatus,
        subscription_expires_at: subscriptionExpiresAt.trim() || null,
        plan_id: planId,
      }),
    onSuccess: async () => {
      toast.showSuccess('تم تحديث الباقة');
      await invalidate();
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  const saveSubscriptionMutation = useMutation({
    mutationFn: () => {
      const current = query.data?.clinic;
      if (!current) {
        throw new Error('تعذر تحميل بيانات العيادة');
      }
      return updatePlatformClinic(clinicId, {
        is_active: current.is_active,
        subscription_status: subscriptionStatus,
        subscription_expires_at: subscriptionExpiresAt.trim() || null,
        plan_id: current.plan_id ?? null,
      });
    },
    onSuccess: async () => {
      toast.showSuccess('تم حفظ إعدادات الاشتراك');
      await invalidate();
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  function confirmSuspend() {
    Alert.alert('تعليق العيادة', 'هل أنت متأكد من تعليق هذه العيادة؟', [
      { text: 'إلغاء', style: 'cancel' },
      {
        text: 'تعليق',
        style: 'destructive',
        onPress: () => suspendMutation.mutate(),
      },
    ]);
  }

  if (query.isLoading) {
    return (
      <ScreenContainer>
        <SafeAreaView style={styles.safe} edges={['top']}>
          <ScreenHeader title="تفاصيل العيادة" />
          <ListScreenSkeleton count={3} />
        </SafeAreaView>
      </ScreenContainer>
    );
  }

  if (query.isError || !query.data) {
    return (
      <ScreenContainer>
        <ErrorView message={formatApiError(query.error)} onRetry={() => void query.refetch()} />
      </ScreenContainer>
    );
  }

  const { clinic, recent_payments, plans } = query.data;
  const busy =
    activateMutation.isPending ||
    suspendMutation.isPending ||
    paymentMutation.isPending ||
    updateMutation.isPending ||
    saveSubscriptionMutation.isPending;

  return (
    <ScreenContainer>
      <SafeAreaView style={styles.safe} edges={['top']}>
        <ScreenHeader title={clinic.name} subtitle={`#${clinic.id}`} />
        <ScrollView contentContainerStyle={styles.content}>
          <Card>
            <View style={styles.headerRow}>
              <Badge label={clinic.subscription_tier_label} color={theme.colors.brand} />
              <Text style={styles.status}>{clinic.is_active ? 'نشطة' : 'معلّقة'}</Text>
            </View>
            {clinic.stripe_status_label ? (
              <Text style={styles.stripeStatus}>{clinic.stripe_status_label}</Text>
            ) : null}
            {clinic.owner ? (
              <>
                <Text style={styles.label}>المالك</Text>
                <Text style={styles.value}>{clinic.owner.name}</Text>
                <Text style={styles.meta}>{clinic.owner.email}</Text>
              </>
            ) : null}
            {clinic.created_at ? (
              <Text style={styles.meta}>تاريخ التسجيل: {formatPlatformDateTime(clinic.created_at)}</Text>
            ) : null}
            {clinic.plan?.name ? <Text style={styles.meta}>الباقة: {clinic.plan.name}</Text> : null}
            {clinic.total_paid != null ? (
              <Text style={styles.totalPaid}>إجمالي المدفوعات: {clinic.total_paid.toFixed(2)}</Text>
            ) : null}
            {clinic.subscription_expires_at ? (
              <Text style={styles.meta}>
                ينتهي الاشتراك: {formatPlatformDateTime(clinic.subscription_expires_at)}
              </Text>
            ) : null}
          </Card>

          <FormSection title="إجراءات سريعة">
            <View style={styles.actions}>
              <Button
                label="تفعيل"
                icon="check"
                onPress={() => activateMutation.mutate()}
                loading={activateMutation.isPending}
                disabled={busy}
              />
              <Button
                label="تعليق"
                variant="danger"
                icon="x"
                onPress={confirmSuspend}
                loading={suspendMutation.isPending}
                disabled={busy}
              />
            </View>
          </FormSection>

          <FormSection title="إعدادات الاشتراك">
            <ChipGroup
              options={['active', 'expired']}
              value={subscriptionStatus}
              onChange={setSubscriptionStatus}
              labels={{ active: 'نشط', expired: 'منتهي' }}
            />
            <DateTimeField
              label="تاريخ انتهاء الاشتراك"
              value={subscriptionExpiresAt}
              onChange={setSubscriptionExpiresAt}
            />
            <Button
              label="حفظ إعدادات الاشتراك"
              icon="check"
              onPress={() => saveSubscriptionMutation.mutate()}
              loading={saveSubscriptionMutation.isPending}
              disabled={busy}
            />
          </FormSection>

          <FormSection title="تغيير الباقة">
            <View style={styles.planList}>
              {plans.map((plan) => (
                <Button
                  key={plan.id}
                  label={`${plan.name}${clinic.plan_id === plan.id ? ' ✓' : ''}`}
                  variant={clinic.plan_id === plan.id ? 'primary' : 'secondary'}
                  compact
                  onPress={() => updateMutation.mutate(plan.id)}
                  disabled={busy || clinic.plan_id === plan.id}
                />
              ))}
            </View>
          </FormSection>

          <FormSection title="تسجيل دفعة يدوية">
            <Input label="المبلغ" value={amount} onChangeText={setAmount} keyboardType="decimal-pad" placeholder="0.00" />
            <DateTimeField label="تاريخ الدفع" value={paidAt} onChange={setPaidAt} />
            <Input label="ملاحظات" value={notes} onChangeText={setNotes} placeholder="اختياري" />
            <DateTimeField
              label="تاريخ انتهاء الاشتراك بعد الدفع"
              value={paymentExpiresAt}
              onChange={setPaymentExpiresAt}
            />
            <Button
              label="تسجيل الدفعة"
              icon="plus"
              onPress={() => paymentMutation.mutate()}
              loading={paymentMutation.isPending}
              disabled={busy || !amount.trim()}
            />
          </FormSection>

          <DateRangeFilter
            title="فلتر سجل المدفوعات"
            subtitle="يُطبَّق على القائمة والتصدير"
            paidFrom={paidFrom}
            paidTo={paidTo}
            onPaidFromChange={setPaidFrom}
            onPaidToChange={setPaidTo}
          />

          <FormSection title="سجل المدفوعات">
            <Button
              label="تصدير مدفوعات العيادة"
              icon="download"
              variant="secondary"
              compact
              loading={exportingPayments}
              onPress={async () => {
                setExportingPayments(true);
                try {
                  await exportPlatformPaymentsCsv(clinicId, paymentFilters);
                  toast.showSuccess('تم تجهيز ملف المدفوعات');
                } catch (error) {
                  toast.showError(formatApiError(error));
                } finally {
                  setExportingPayments(false);
                }
              }}
            />
            {recent_payments.length === 0 ? (
              <Text style={styles.empty}>لا توجد مدفوعات</Text>
            ) : (
              recent_payments.map((payment) => (
                <View key={payment.id} style={styles.paymentRow}>
                  <View style={styles.paymentTop}>
                    <Text style={styles.paymentAmount}>{payment.amount}</Text>
                    <PaymentSourceBadge source={payment.source} />
                  </View>
                  <Text style={styles.meta}>{formatPlatformDateTime(payment.paid_at)}</Text>
                  {payment.notes ? <Text style={styles.notes}>{payment.notes}</Text> : null}
                </View>
              ))
            )}
          </FormSection>
        </ScrollView>
      </SafeAreaView>
    </ScreenContainer>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    safe: { flex: 1 },
    content: { padding: theme.spacing.lg, gap: theme.spacing.lg, paddingBottom: theme.spacing.xxl },
    headerRow: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: theme.spacing.md,
    },
    status: { ...theme.typography.body, color: theme.colors.text },
    stripeStatus: {
      ...theme.typography.caption,
      color: theme.colors.info,
      textAlign: 'right',
      marginBottom: theme.spacing.sm,
      fontWeight: '600',
    },
    label: { ...theme.typography.caption, color: theme.colors.textMuted, textAlign: 'right' },
    value: { ...theme.typography.subtitle, color: theme.colors.text, textAlign: 'right' },
    meta: { ...theme.typography.caption, color: theme.colors.textMuted, textAlign: 'right', marginTop: 4 },
    totalPaid: {
      ...theme.typography.subtitle,
      color: theme.colors.brand,
      textAlign: 'right',
      marginTop: theme.spacing.sm,
    },
    actions: { gap: theme.spacing.sm },
    planList: { gap: theme.spacing.sm },
    paymentRow: {
      paddingVertical: theme.spacing.sm,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
      alignItems: 'flex-end',
      gap: 4,
    },
    paymentTop: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      width: '100%',
      gap: theme.spacing.sm,
    },
    paymentAmount: { ...theme.typography.subtitle, color: theme.colors.brand },
    notes: { ...theme.typography.caption, color: theme.colors.text, textAlign: 'right' },
    empty: { ...theme.typography.body, color: theme.colors.textMuted, textAlign: 'center' },
  });
}
