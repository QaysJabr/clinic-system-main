import { useRouter } from 'expo-router';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createPatient } from '@/api/services/patients.service';
import { StackFormScreen } from '@/components/layout/FormScreen';
import { PatientForm } from '@/features/patients/components/PatientForm';
import { queryKeys } from '@/lib/query-client';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';
import { fireHapticSuccess } from '@/utils/haptics';

export default function CreatePatientScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();

  const mutation = useMutation({
    mutationFn: createPatient,
    onSuccess: async (patient) => {
      fireHapticSuccess();
      toast.showSuccess(`تم إضافة ${patient.full_name}`);
      await queryClient.invalidateQueries({ queryKey: ['patients'] });
      router.replace({ pathname: '/(app)/patients/[id]', params: { id: String(patient.id) } });
    },
    onError: (error) => toast.showError(formatApiError(error, 'تعذر إضافة المريض')),
  });

  return (
    <StackFormScreen title="مريض جديد" subtitle="تسجيل سريع من الاستقبال">
      <PatientForm
        submitLabel="حفظ المريض"
        loading={mutation.isPending}
        onSubmit={async (payload) => {
          await mutation.mutateAsync(payload);
        }}
      />
    </StackFormScreen>
  );
}
