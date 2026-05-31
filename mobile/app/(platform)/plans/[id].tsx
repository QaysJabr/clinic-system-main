import { useMemo } from 'react';
import { StyleSheet, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  deletePlatformPlan,
  fetchPlatformPlans,
  updatePlatformPlan,
} from '@/api/services/platform.service';
import { ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ScreenHeader } from '@/components/layout/ScreenHeader';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { Button } from '@/components/ui/primitives';
import { PlanForm } from '@/features/platform/components/PlanForm';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';

export default function EditPlatformPlanScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const planId = Number(id);
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const query = useQuery({
    queryKey: queryKeys.platformPlans,
    queryFn: fetchPlatformPlans,
  });

  const plan = query.data?.find((item) => item.id === planId);

  const updateMutation = useMutation({
    mutationFn: (payload: Parameters<typeof updatePlatformPlan>[1]) => updatePlatformPlan(planId, payload),
    onSuccess: async () => {
      toast.showSuccess('تم تحديث الباقة');
      await queryClient.invalidateQueries({ queryKey: queryKeys.platformPlans });
      router.back();
    },
    onError: (error) => toast.showError(formatApiError(error, 'تعذر تحديث الباقة')),
  });

  const deleteMutation = useMutation({
    mutationFn: () => deletePlatformPlan(planId),
    onSuccess: async () => {
      toast.showSuccess('تم حذف الباقة');
      await queryClient.invalidateQueries({ queryKey: queryKeys.platformPlans });
      router.back();
    },
    onError: (error) => toast.showError(formatApiError(error, 'تعذر حذف الباقة')),
  });

  if (query.isLoading) {
    return (
      <ScreenContainer>
        <SafeAreaView style={styles.safe} edges={['top']}>
          <ScreenHeader title="تعديل الباقة" />
          <ListScreenSkeleton count={2} />
        </SafeAreaView>
      </ScreenContainer>
    );
  }

  if (query.isError || !plan) {
    return (
      <ScreenContainer>
        <ErrorView message={formatApiError(query.error, 'تعذر تحميل الباقة')} onRetry={() => void query.refetch()} />
      </ScreenContainer>
    );
  }

  return (
    <ScreenContainer>
      <SafeAreaView style={styles.safe} edges={['top']}>
        <ScreenHeader title="تعديل الباقة" subtitle={plan.name} />
        <PlanForm
          plan={plan}
          submitLabel="حفظ التعديلات"
          loading={updateMutation.isPending}
          onSubmit={async (payload) => {
            await updateMutation.mutateAsync(payload);
          }}
        />
        <View style={styles.deleteWrap}>
          <Button
            label="حذف الباقة"
            variant="danger"
            icon="x"
            loading={deleteMutation.isPending}
            disabled={plan.clinics_count > 0 || updateMutation.isPending}
            onPress={() => deleteMutation.mutate()}
          />
        </View>
      </SafeAreaView>
    </ScreenContainer>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    safe: { flex: 1 },
    deleteWrap: {
      paddingHorizontal: theme.spacing.lg,
      paddingBottom: theme.spacing.xxl,
    },
  });
}
