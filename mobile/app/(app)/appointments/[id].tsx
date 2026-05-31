import { View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  checkInAppointment,
  fetchAppointment,
  updateAppointment,
} from '@/api/services/appointments.service';
import { canCheckInAppointment } from '@/auth/permissions';
import {
  FormScreenError,
  FormScreenLoading,
  StackFormScreen,
} from '@/components/layout/FormScreen';
import { AppointmentForm } from '@/features/appointments/components/AppointmentForm';
import { queryKeys } from '@/lib/query-client';
import { useToast } from '@/providers/ToastProvider';
import { Button } from '@/components/ui/primitives';
import { fireHapticSuccess } from '@/utils/haptics';
import { formatApiError } from '@/utils/format';

export default function EditAppointmentScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const appointmentId = Number(id);
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();

  const query = useQuery({
    queryKey: queryKeys.appointment(appointmentId),
    queryFn: () => fetchAppointment(appointmentId),
    enabled: Number.isFinite(appointmentId) && appointmentId > 0,
  });

  const mutation = useMutation({
    mutationFn: (payload: Parameters<typeof updateAppointment>[1]) =>
      updateAppointment(appointmentId, payload),
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['appointments'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.appointment(appointmentId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
      ]);
      toast.showSuccess('تم تحديث الموعد بنجاح');
      router.back();
    },
    onError: (error) => {
      toast.showError(formatApiError(error, 'تعذر تحديث الموعد'));
    },
  });

  const checkInMutation = useMutation({
    mutationFn: () => checkInAppointment(appointmentId),
    onSuccess: async () => {
      fireHapticSuccess();
      toast.showSuccess('تم تسجيل حضور المريض');
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['appointments'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.appointment(appointmentId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
        queryClient.invalidateQueries({ queryKey: queryKeys.visits() }),
      ]);
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  if (query.isLoading) {
    return <FormScreenLoading title="تعديل الموعد" />;
  }

  if (query.isError || !query.data) {
    return (
      <FormScreenError
        title="تعديل الموعد"
        message={formatApiError(query.error)}
        onRetry={() => void query.refetch()}
      />
    );
  }

  const checkInHeader =
    canCheckInAppointment(query.data.status, query.data.visit_id) ? (
      <View style={{ paddingHorizontal: 16, paddingTop: 8, paddingBottom: 4 }}>
        <Button
          label="تسجيل حضور → طابور الزيارات"
          icon="check"
          onPress={() => checkInMutation.mutate()}
          loading={checkInMutation.isPending}
        />
      </View>
    ) : null;

  return (
    <StackFormScreen title="تعديل الموعد" subtitle={`#${query.data.id}`} header={checkInHeader}>
      <AppointmentForm
        appointment={query.data}
        submitLabel="حفظ التعديلات"
        loading={mutation.isPending}
        onSubmit={async (payload) => {
          await mutation.mutateAsync(payload);
        }}
      />
    </StackFormScreen>
  );
}
