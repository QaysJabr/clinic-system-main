import { SafeAreaView } from 'react-native-safe-area-context';
import { useRouter } from 'expo-router';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createPlatformPlan } from '@/api/services/platform.service';
import { ScreenContainer } from '@/components/layout/Screen';
import { ScreenHeader } from '@/components/layout/ScreenHeader';
import { PlanForm } from '@/features/platform/components/PlanForm';
import { queryKeys } from '@/lib/query-client';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';
import { StyleSheet } from 'react-native';

export default function CreatePlatformPlanScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();

  const mutation = useMutation({
    mutationFn: createPlatformPlan,
    onSuccess: async () => {
      toast.showSuccess('تم إنشاء الباقة');
      await queryClient.invalidateQueries({ queryKey: queryKeys.platformPlans });
      router.back();
    },
    onError: (error) => toast.showError(formatApiError(error, 'تعذر إنشاء الباقة')),
  });

  return (
    <ScreenContainer>
      <SafeAreaView style={styles.safe} edges={['top']}>
        <ScreenHeader title="باقة جديدة" subtitle="إضافة باقة اشتراك" />
        <PlanForm
          submitLabel="إنشاء الباقة"
          loading={mutation.isPending}
          onSubmit={async (payload) => {
            await mutation.mutateAsync(payload);
          }}
        />
      </SafeAreaView>
    </ScreenContainer>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1 },
});
