import { useMemo } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { useRouter, type Href } from 'expo-router';
import { Panel } from '@/components/layout/Panel';
import { AppIcon } from '@/components/ui/AppIcon';
import { can, PERMS } from '@/auth/permissions';
import type { ComponentProps } from 'react';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { StatCard } from '@/components/ui/primitives';
import { canRecordPayment, resolveClinicPersona } from '@/auth/permissions';
import { DashboardAlerts } from '@/features/clinic/components/DashboardAlerts';
import { InvoiceCard } from '@/features/clinic/components/InvoiceCard';
import { QuickActionsBar } from '@/features/clinic/components/QuickActionsBar';
import { AppointmentCard } from '@/features/appointments/components/AppointmentCard';
import { VisitQueueCard } from '@/features/visits/components/VisitQueueCard';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { DashboardData, User } from '@/types/api';

type AppRouter = ReturnType<typeof useRouter>;

export function ClinicDashboardContent({
  data,
  user,
  router,
  onCheckIn,
  checkingInId,
}: {
  data: DashboardData;
  user: User | null | undefined;
  router: AppRouter;
  onCheckIn?: (appointmentId: number) => void;
  checkingInId?: number | null;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const persona = data.persona ?? resolveClinicPersona(user);
  const currency = data.currency ? ` ${data.currency}` : '';

  const quickActions = buildQuickActions(persona, router, data, user, theme.colors);

  return (
    <>
      <DashboardAlerts alerts={data.alerts} />
      <QuickActionsBar actions={quickActions} />

      <View style={styles.statsGrid}>{renderStats(persona, data, theme, currency)}</View>

      {persona === 'accountant' || persona === 'admin' ? (
        <Panel
          title="فواتير تحتاج متابعة"
          actionLabel={data.recent_invoices.length > 0 ? 'عرض الكل' : undefined}
          onAction={data.recent_invoices.length > 0 ? () => router.push('/(app)/finance' as Href) : undefined}
        >
          {data.recent_invoices.length === 0 ? (
            <Text style={styles.emptyInline}>لا توجد فواتير مفتوحة</Text>
          ) : (
            data.recent_invoices.slice(0, 5).map((invoice, index) => (
              <AnimatedListItem key={invoice.id} index={index}>
                <InvoiceCard
                  invoice={invoice}
                  onPress={() => router.push({ pathname: '/(app)/finance/[id]', params: { id: String(invoice.id) } })}
                />
              </AnimatedListItem>
            ))
          )}
        </Panel>
      ) : null}

      {persona !== 'accountant' ? (
        <Panel
          title={persona === 'doctor' ? 'مواعيدي اليوم' : 'مواعيد اليوم'}
          actionLabel="عرض الكل"
          onAction={() => router.push('/(app)/appointments')}
        >
          {data.today_appointments.length === 0 ? (
            <Text style={styles.emptyInline}>لا توجد مواعيد اليوم</Text>
          ) : (
            data.today_appointments.map((item, index) => (
              <AnimatedListItem key={item.id} index={index}>
                <AppointmentCard
                  appointment={item}
                  onPress={() => router.push(`/(app)/appointments/${item.id}`)}
                  onPatientPress={(patientId) =>
                    router.push({ pathname: '/(app)/patients/[id]', params: { id: String(patientId) } })
                  }
                  onCheckIn={onCheckIn ? () => onCheckIn(item.id) : undefined}
                  checkInLoading={checkingInId === item.id}
                />
              </AnimatedListItem>
            ))
          )}
        </Panel>
      ) : null}

      {persona === 'doctor' || persona === 'receptionist' || persona === 'admin' ? (
        <Panel
          title={persona === 'doctor' ? 'طابوري' : 'طابور الزيارات'}
          actionLabel="عرض الطابور"
          onAction={() => router.push('/(app)/visits')}
        >
          {data.visit_queue.length === 0 ? (
            <Text style={styles.emptyInline}>الطابور فارغ</Text>
          ) : (
              data.visit_queue.map((item) => (
                <VisitQueueCard
                  key={item.id}
                  visit={item}
                  onPress={() =>
                    router.push({ pathname: '/(app)/visits/[id]', params: { id: String(item.id) } })
                  }
                />
              ))
          )}
        </Panel>
      ) : null}

      {persona === 'doctor' && data.doctor_earning ? (
        <Panel title="أرباحي">
          <View style={styles.earningRow}>
            <StatCard label="إجمالي" value={data.doctor_earning.total.toFixed(2)} accent={theme.colors.brand} />
            <StatCard label="معلّق" value={data.doctor_earning.pending.toFixed(2)} accent={theme.colors.warning} />
            <StatCard label="مدفوع" value={data.doctor_earning.paid.toFixed(2)} accent={theme.colors.success} />
          </View>
        </Panel>
      ) : null}

      {persona === 'admin' && data.financial ? (
        <Panel title="لمحة مالية سريعة">
          <View style={styles.earningRow}>
            <StatCard
              label="تحصيل اليوم"
              value={`${data.financial.today_cash_collected.toFixed(2)}${currency}`}
              icon="file-text"
              accent={theme.colors.success}
            />
            <StatCard
              label="ذمم مدينة"
              value={`${data.financial.accounts_receivable.toFixed(2)}${currency}`}
              icon="alert-circle"
              accent={theme.colors.warning}
            />
          </View>
        </Panel>
      ) : null}
    </>
  );
}

function buildQuickActions(
  persona: DashboardData['persona'],
  router: AppRouter,
  data: DashboardData,
  user: User | null | undefined,
  colors: ReturnType<typeof useAppTheme>['theme']['colors'],
) {
  type QuickAction = {
    label: string;
    icon: ComponentProps<typeof AppIcon>['name'];
    onPress: () => void;
    accent?: string;
  };

  if (persona === 'receptionist') {
    const actions: QuickAction[] = [
      { label: 'موعد جديد', icon: 'calendar' as const, onPress: () => router.push('/(app)/appointments/create') },
      { label: 'طابور الزيارات', icon: 'users' as const, onPress: () => router.push('/(app)/visits'), accent: colors.warning },
    ];
    if (can(user, PERMS.MANAGE_PATIENTS)) {
      actions.unshift({
        label: 'مريض جديد',
        icon: 'user' as const,
        onPress: () => router.push('/(app)/patients/create' as Href),
      });
    }
    return actions;
  }

  if (persona === 'doctor') {
    return [
      { label: 'طابوري', icon: 'users' as const, onPress: () => router.push('/(app)/visits') },
      { label: 'مواعيدي', icon: 'calendar' as const, onPress: () => router.push('/(app)/appointments'), accent: colors.info },
    ];
  }

  if (persona === 'accountant') {
    return [
      { label: 'الفواتير', icon: 'file-text' as const, onPress: () => router.push('/(app)/finance' as Href) },
      {
        label: 'فاتورة مفتوحة',
        icon: 'alert-circle' as const,
        onPress: () => router.push('/(app)/finance?status=unpaid' as Href),
        accent: colors.danger,
      },
    ];
  }

  return [
    ...(can(user, PERMS.MANAGE_PATIENTS)
      ? [{ label: 'مريض جديد', icon: 'user' as const, onPress: () => router.push('/(app)/patients/create' as Href) }]
      : []),
    { label: 'المواعيد', icon: 'calendar' as const, onPress: () => router.push('/(app)/appointments') },
    { label: 'الزيارات', icon: 'users' as const, onPress: () => router.push('/(app)/visits'), accent: colors.warning },
    ...(data.stats.open_invoices > 0
      ? [{ label: 'الفواتير', icon: 'file-text' as const, onPress: () => router.push('/(app)/finance' as Href), accent: colors.success }]
      : []),
  ];
}

function renderStats(
  persona: DashboardData['persona'],
  data: DashboardData,
  theme: ReturnType<typeof useAppTheme>['theme'],
  currency: string,
) {
  if (persona === 'accountant') {
    return (
      <>
        <StatCard label="فواتير مفتوحة" value={data.stats.open_invoices} icon="file-text" accent={theme.colors.danger} />
        {data.financial ? (
          <>
            <StatCard
              label="تحصيل اليوم"
              value={`${data.financial.today_cash_collected.toFixed(2)}${currency}`}
              icon="file-text"
              accent={theme.colors.success}
            />
            <StatCard
              label="ذمم مدينة"
              value={`${data.financial.accounts_receivable.toFixed(2)}${currency}`}
              icon="alert-circle"
              accent={theme.colors.warning}
            />
          </>
        ) : null}
      </>
    );
  }

  if (persona === 'doctor') {
    return (
      <>
        <StatCard label="مواعيدي" value={data.stats.today_appointments} icon="calendar" accent={theme.colors.brand} />
        <StatCard label="زياراتي" value={data.stats.today_visits} icon="users" accent={theme.colors.info} />
        <StatCard label="بالانتظار" value={data.stats.today_visits_waiting} icon="clock" accent={theme.colors.warning} />
      </>
    );
  }

  if (persona === 'admin') {
    return (
      <>
        <StatCard label="مواعيد اليوم" value={data.stats.today_appointments} icon="calendar" accent={theme.colors.brand} />
        <StatCard label="بالانتظار" value={data.stats.today_visits_waiting} icon="clock" accent={theme.colors.warning} />
        <StatCard label="فواتير مفتوحة" value={data.stats.open_invoices} icon="file-text" accent={theme.colors.danger} />
        <StatCard label="زيارات اليوم" value={data.stats.today_visits} icon="users" accent={theme.colors.info} />
      </>
    );
  }

  return (
    <>
      <StatCard label="مواعيد اليوم" value={data.stats.today_appointments} icon="calendar" accent={theme.colors.brand} />
      <StatCard label="بالانتظار" value={data.stats.today_visits_waiting} icon="clock" accent={theme.colors.warning} />
      <StatCard label="زيارات اليوم" value={data.stats.today_visits} icon="users" accent={theme.colors.info} />
      <StatCard label="فواتير مفتوحة" value={data.stats.open_invoices} icon="file-text" accent={theme.colors.success} />
    </>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    statsGrid: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.md,
      paddingHorizontal: theme.spacing.lg,
      marginTop: theme.spacing.sm,
    },
    earningRow: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.md,
    },
    emptyInline: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
      textAlign: 'center',
      paddingVertical: theme.spacing.lg,
    },
  });
}
