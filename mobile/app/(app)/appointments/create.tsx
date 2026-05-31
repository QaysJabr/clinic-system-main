import { useRouter } from 'expo-router';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createAppointment } from '@/api/services/appointments.service';
import { StackFormScreen } from '@/components/layout/FormScreen';
import { AppointmentForm } from '@/features/appointments/components/AppointmentForm';
import { queryKeys } from '@/lib/query-client';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';
import { fireHapticSuccess } from '@/utils/haptics';

export default function CreateAppointmentScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();

  const mutation = useMutation({
    mutationFn: createAppointment,
    onSuccess: async () => {
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['appointments'] }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
      ]);
      toast.showSuccess('تم إنشاء الموعد بنجاح');
      fireHapticSuccess();
      router.back();
    },
    onError: (error) => {
      toast.showError(formatApiError(error, 'تعذر إنشاء الموعد'));
    },
  });

  return (
    <StackFormScreen title="موعد جديد" subtitle="إضافة موعد جديد للعيادة">
      <AppointmentForm
        submitLabel="حفظ الموعد"
        loading={mutation.isPending}
        onSubmit={async (payload) => {
          await mutation.mutateAsync(payload);
        }}
      />
    </StackFormScreen>
  );
}
