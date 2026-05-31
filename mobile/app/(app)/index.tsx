import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet } from 'react-native';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { checkInAppointment } from '@/api/services/appointments.service';
import { fetchDashboard } from '@/api/services/dashboard.service';
import { useAuth } from '@/auth/AuthContext';
import { clinicPersonaLabel } from '@/auth/permissions';
import { ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { DashboardSkeleton } from '@/components/ui/Skeleton';
import { ClinicDashboardContent } from '@/features/clinic/components/ClinicDashboardContent';
import { ClinicHero } from '@/features/clinic/components/ClinicHero';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';
import { fireHapticSuccess } from '@/utils/haptics';

export default function DashboardScreen() {
  const { user } = useAuth();
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const router = useRouter();
  const toast = useToast();
  const queryClient = useQueryClient();
  const [checkingInId, setCheckingInId] = useState<number | null>(null);

  const query = useQuery({
    queryKey: queryKeys.dashboard,
    queryFn: fetchDashboard,
  });

  const checkInMutation = useMutation({
    mutationFn: checkInAppointment,
    onSuccess: async () => {
      toast.showSuccess('تم تسجيل حضور المريض');
      fireHapticSuccess();
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
        queryClient.invalidateQueries({ queryKey: ['appointments'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.visits() }),
      ]);
    },
    onError: (error) => toast.showError(formatApiError(error)),
    onSettled: () => setCheckingInId(null),
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
        <ErrorView message={formatApiError(query.error)} onRetry={() => void query.refetch()} />
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
            onRefresh={() => void query.refetch()}
          />
        }
      >
        <ClinicHero
          userName={user?.name}
          clinicName={data.clinic.name}
          personaLabel={clinicPersonaLabel(user, data.persona_label)}
          onSettingsPress={() => router.push('/(app)/settings')}
        />
        <ClinicDashboardContent
          data={data}
          user={user}
          router={router}
          checkingInId={checkingInId}
          onCheckIn={(appointmentId) => {
            setCheckingInId(appointmentId);
            checkInMutation.mutate(appointmentId);
          }}
        />
      </ScrollView>
    </ScreenContainer>
  );
}

const createStyles = (theme: ReturnType<typeof useAppTheme>['theme']) =>
  StyleSheet.create({
    content: { paddingBottom: theme.spacing.xxl, gap: theme.spacing.lg },
  });
