import { useMemo, useState } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useRouter, type Href } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { exportPlatformPaymentsCsv, fetchPlatformDashboard } from '@/api/services/platform.service';
import { useAuth } from '@/auth/AuthContext';
import { Panel } from '@/components/layout/Panel';
import { ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { AppHero } from '@/components/ui/AppHero';
import { DashboardSkeleton } from '@/components/ui/Skeleton';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { Button, StatCard } from '@/components/ui/primitives';
import { PaymentSourceBadge } from '@/features/platform/components/PaymentSourceBadge';
import { PlatformClinicCard } from '@/features/platform/components/PlatformClinicCard';
import { QuickActionGrid, RevenueChart } from '@/features/platform/components/PlatformInsights';
import { formatPlatformDateTime } from '@/features/platform/utils/platform-format';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';

export default function PlatformDashboardScreen() {
  const { user } = useAuth();
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const router = useRouter();
  const toast = useToast();
  const [exportingPayments, setExportingPayments] = useState(false);
  const query = useQuery({
    queryKey: queryKeys.platformDashboard,
    queryFn: fetchPlatformDashboard,
  });

  if (query.isLoading) {
    return (
      <ScreenContainer>
        <DashboardSkeleton />
      </ScreenContainer>
    );
  }

  if (query.isError || !query.data) {
    return (
      <ScreenContainer>
        <ErrorView
          message={formatApiError(query.error)}
          onRetry={() => {
            void query.refetch();
          }}
        />
      </ScreenContainer>
    );
  }

  const data = query.data;

  return (
    <ScreenContainer>
      <ScrollView
        contentContainerStyle={styles.content}
        refreshControl={
          <RefreshControlThemed
            refreshing={query.isRefetching}
            onRefresh={() => {
              void query.refetch();
            }}
          />
        }
      >
        <AppHero
          title="لوحة المنصّة"
          subtitle={user?.name}
          subtitleIcon="circle-user"
          onSettingsPress={() => router.push('/(platform)/settings' as Href)}
        />

          <View style={styles.statsGrid}>
            <StatCard label="إجمالي الإيرادات" value={data.total_revenue.toFixed(2)} icon="file-text" accent={theme.colors.brand} />
            <StatCard label="إيراد الشهر" value={data.monthly_revenue.toFixed(2)} icon="calendar" accent={theme.colors.info} hint={`يدوي ${data.monthly_revenue_manual.toFixed(2)} · Stripe ${data.monthly_revenue_stripe.toFixed(2)}`} />
            <StatCard label="إجمالي العيادات" value={data.total_clinics} icon="building-2" accent={theme.colors.success} hint={`+${data.new_clinics_this_month} هذا الشهر`} />
            <StatCard label="عيادات نشطة" value={data.active_clinics} icon="users" accent={theme.colors.success} />
            <StatCard label="معلّقة" value={data.suspended_clinics} icon="x" accent={theme.colors.textMuted} />
            <StatCard label="منتهية" value={data.expired_clinics} icon="alert-circle" accent={theme.colors.danger} />
            <StatCard label={`تنتهي خلال ${data.expiring_soon_days} أيام`} value={data.expiring_soon_count} icon="clock" accent={theme.colors.warning} />
          </View>

          <QuickActionGrid
            actions={[
              {
                label: 'إدارة العيادات',
                icon: 'building-2',
                onPress: () => router.push('/(platform)/clinics' as Href),
              },
              {
                label: 'الباقات',
                icon: 'file-text',
                onPress: () => router.push('/(platform)/plans' as Href),
                accent: theme.colors.info,
              },
              {
                label: 'تنتهي قريباً',
                icon: 'clock',
                onPress: () => router.push('/(platform)/clinics?filter=expiring_soon' as Href),
                accent: theme.colors.warning,
              },
              {
                label: 'عيادات منتهية',
                icon: 'alert-circle',
                onPress: () => router.push('/(platform)/clinics?filter=expired' as Href),
                accent: theme.colors.danger,
              },
            ]}
          />

          <Panel title={`الإيرادات — آخر ${data.revenue_by_month.length} أشهر`}>
            <RevenueChart data={data.revenue_by_month} />
          </Panel>

          <Panel
            title="عيادات تنتهي قريباً"
            actionLabel="عرض الكل"
            onAction={() => router.push('/(platform)/clinics?filter=expiring_soon' as Href)}
          >
            {data.clinics_expiring_soon.length === 0 ? (
              <Text style={styles.emptyInline}>لا توجد عيادات</Text>
            ) : (
              data.clinics_expiring_soon.map((item, index) => (
                <AnimatedListItem key={item.id} index={index}>
                  <PlatformClinicCard
                    clinic={item}
                    onPress={() =>
                      router.push({ pathname: '/(platform)/clinics/[id]', params: { id: String(item.id) } })
                    }
                  />
                </AnimatedListItem>
              ))
            )}
          </Panel>

          <Panel
            title="عيادات منتهية الاشتراك"
            actionLabel="عرض الكل"
            onAction={() => router.push('/(platform)/clinics?filter=expired' as Href)}
          >
            {data.clinics_expired.length === 0 ? (
              <Text style={styles.emptyInline}>لا توجد عيادات</Text>
            ) : (
              data.clinics_expired.map((item, index) => (
                <AnimatedListItem key={item.id} index={index}>
                  <PlatformClinicCard
                    clinic={item}
                    onPress={() =>
                      router.push({ pathname: '/(platform)/clinics/[id]', params: { id: String(item.id) } })
                    }
                  />
                </AnimatedListItem>
              ))
            )}
          </Panel>

          <Panel title="آخر المدفوعات">
            <Button
              label="تصدير المدفوعات CSV"
              icon="download"
              variant="secondary"
              compact
              loading={exportingPayments}
              onPress={async () => {
                setExportingPayments(true);
                try {
                  await exportPlatformPaymentsCsv();
                  toast.showSuccess('تم تجهيز ملف المدفوعات');
                } catch (error) {
                  toast.showError(formatApiError(error));
                } finally {
                  setExportingPayments(false);
                }
              }}
            />
            {data.recent_payments.length === 0 ? (
              <Text style={styles.emptyInline}>لا توجد مدفوعات</Text>
            ) : (
              data.recent_payments.map((payment) => (
                <PressablePaymentRow
                  key={payment.id}
                  amount={payment.amount}
                  clinicName={payment.clinic?.name ?? `#${payment.clinic_id}`}
                  paidAt={payment.paid_at}
                  source={payment.source}
                  onPress={() =>
                    router.push({ pathname: '/(platform)/clinics/[id]', params: { id: String(payment.clinic_id) } })
                  }
                  styles={styles}
                />
              ))
            )}
          </Panel>
        </ScrollView>
    </ScreenContainer>
  );
}

function PressablePaymentRow({
  amount,
  clinicName,
  paidAt,
  source,
  onPress,
  styles,
}: {
  amount: string;
  clinicName: string;
  paidAt: string | null;
  source: string;
  onPress: () => void;
  styles: ReturnType<typeof createStyles>;
}) {
  return (
    <View style={styles.paymentRow}>
      <Button label="إدارة" variant="secondary" compact onPress={onPress} />
      <View style={styles.paymentBody}>
        <View style={styles.paymentTop}>
          <Text style={styles.paymentAmount}>{amount}</Text>
          <PaymentSourceBadge source={source} />
        </View>
        <Text style={styles.paymentClinic}>{clinicName}</Text>
        <Text style={styles.paymentDate}>{formatPlatformDateTime(paidAt)}</Text>
      </View>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    content: { paddingBottom: theme.spacing.xxl, gap: theme.spacing.lg },
    statsGrid: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.md,
      paddingHorizontal: theme.spacing.lg,
      marginTop: -theme.spacing.lg,
    },
    emptyInline: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
      textAlign: 'center',
      paddingVertical: theme.spacing.lg,
    },
    paymentRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
      paddingVertical: theme.spacing.sm,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
    },
    paymentBody: { flex: 1, alignItems: 'flex-end' },
    paymentTop: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    paymentAmount: {
      ...theme.typography.subtitle,
      color: theme.colors.brand,
    },
    paymentClinic: { ...theme.typography.body, color: theme.colors.text },
    paymentDate: { ...theme.typography.caption, color: theme.colors.textMuted },
  });
}
