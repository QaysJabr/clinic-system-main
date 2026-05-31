import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { fetchDoctors } from '@/api/services/doctors.service';
import { FormSection } from '@/components/layout/FormSection';
import { DateField, TimeField } from '@/components/ui/DateTimeField';
import { Button, Input } from '@/components/ui/primitives';
import { ChipGroup, OptionList } from '@/components/ui/ChipGroup';
import { PatientPickerModal } from '@/features/appointments/components/PatientPickerModal';
import { useFormScrollPadding } from '@/components/layout/FormScreen';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';
import { queryKeys } from '@/lib/query-client';
import type { Appointment, AppointmentPayload, AppointmentStatus } from '@/types/api';
import { appointmentStatusLabels } from '@/utils/format';

const statusOptions: AppointmentStatus[] = [
  'scheduled',
  'confirmed',
  'checked_in',
  'in_progress',
  'completed',
  'cancelled',
  'no_show',
];

function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}

function buildPayload(state: FormState): AppointmentPayload {
  return {
    patient_id: state.patientId!,
    doctor_id: state.doctorId!,
    appointment_date: state.appointmentDate,
    start_time: state.startTime,
    end_time: state.endTime || null,
    status: state.status,
    reason: state.reason.trim() || null,
    notes: state.notes.trim() || null,
  };
}

interface FormState {
  patientId: number | null;
  doctorId: number | null;
  appointmentDate: string;
  startTime: string;
  endTime: string;
  status: AppointmentStatus;
  reason: string;
  notes: string;
}

function initialState(appointment?: Appointment): FormState {
  return {
    patientId: appointment?.patient_id ?? null,
    doctorId: appointment?.doctor_id ?? null,
    appointmentDate: appointment?.appointment_date ?? todayIso(),
    startTime: appointment?.start_time ?? '09:00',
    endTime: appointment?.end_time ?? '09:30',
    status: appointment?.status ?? 'scheduled',
    reason: appointment?.reason ?? '',
    notes: appointment?.notes ?? '',
  };
}

export function AppointmentForm({
  appointment,
  onSubmit,
  loading,
  submitLabel,
}: {
  appointment?: Appointment;
  onSubmit: (payload: AppointmentPayload) => Promise<void>;
  loading?: boolean;
  submitLabel: string;
}) {
  const [form, setForm] = useState<FormState>(() => initialState(appointment));
  const [patientModalOpen, setPatientModalOpen] = useState(false);
  const [selectedPatientName, setSelectedPatientName] = useState<string | null>(
    appointment?.patient?.full_name ?? null,
  );
  const [error, setError] = useState<string | null>(null);
  const { theme } = useAppTheme();
  const scrollPadding = useFormScrollPadding();
  const styles = useMemo(() => createStyles(theme, scrollPadding), [theme, scrollPadding]);

  const doctorsQuery = useQuery({
    queryKey: queryKeys.doctors,
    queryFn: () => fetchDoctors(),
  });

  const selectedPatientLabel = useMemo(() => {
    if (selectedPatientName) {
      return selectedPatientName;
    }
    if (appointment?.patient && form.patientId === appointment.patient_id) {
      return appointment.patient.full_name;
    }
    return null;
  }, [appointment, form.patientId, selectedPatientName]);

  async function handleSubmit() {
    setError(null);
    if (!form.patientId || !form.doctorId) {
      setError('اختر المريض والطبيب');
      return;
    }
    try {
      await onSubmit(buildPayload(form));
    } catch (err) {
      const { formatApiError } = await import('@/utils/format');
      setError(formatApiError(err, 'تعذر حفظ الموعد'));
    }
  }

  const doctorItems =
    doctorsQuery.data?.map((d) => ({ id: d.id, label: d.full_name })) ?? [];

  return (
    <>
      <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
        <FormSection title="المريض" subtitle="اختر المريض من قائمة البحث">
          {selectedPatientLabel ? (
            <View style={styles.selectedBox}>
              <Text style={styles.selectedLabel}>المختار</Text>
              <Text style={styles.selected}>{selectedPatientLabel}</Text>
            </View>
          ) : null}
          <Button
            label={selectedPatientLabel ? 'تغيير المريض' : 'اختيار مريض'}
            variant="secondary"
            icon="search"
            onPress={() => setPatientModalOpen(true)}
          />
        </FormSection>

      <FormSection title="الطبيب" subtitle="اختر الطبيب المعالج">
        <OptionList
          items={doctorItems}
          selectedId={form.doctorId}
          onSelect={(id) => setForm((prev) => ({ ...prev, doctorId: id }))}
          emptyLabel="لا يوجد أطباء"
        />
      </FormSection>

      <FormSection title="الموعد" subtitle="حدد التاريخ والوقت">
        <DateField
          label="التاريخ"
          value={form.appointmentDate}
          onChange={(appointmentDate) => setForm((prev) => ({ ...prev, appointmentDate }))}
        />
        <View style={styles.row}>
          <View style={styles.half}>
            <TimeField
              label="من"
              value={form.startTime}
              onChange={(startTime) => setForm((prev) => ({ ...prev, startTime }))}
            />
          </View>
          <View style={styles.half}>
            <TimeField
              label="إلى"
              value={form.endTime}
              onChange={(endTime) => setForm((prev) => ({ ...prev, endTime }))}
            />
          </View>
        </View>
      </FormSection>

      <FormSection title="التفاصيل" subtitle="الحالة والسبب والملاحظات">
        <Text style={styles.fieldLabel}>الحالة</Text>
        <ChipGroup
          options={statusOptions}
          value={form.status}
          onChange={(status) => setForm((prev) => ({ ...prev, status }))}
          labels={appointmentStatusLabels}
        />
        <Input
          label="السبب"
          value={form.reason}
          onChangeText={(reason) => setForm((prev) => ({ ...prev, reason }))}
          multiline
          placeholder="سبب الزيارة"
        />
        <Input
          label="ملاحظات"
          value={form.notes}
          onChangeText={(notes) => setForm((prev) => ({ ...prev, notes }))}
          multiline
          placeholder="ملاحظات إضافية"
        />
      </FormSection>

      {error ? (
        <View style={styles.errorBox}>
          <Text style={styles.error}>{error}</Text>
        </View>
      ) : null}
      <Button label={submitLabel} onPress={() => void handleSubmit()} loading={loading} />
      </ScrollView>

      <PatientPickerModal
        visible={patientModalOpen}
        selectedId={form.patientId}
        onClose={() => setPatientModalOpen(false)}
        onSelect={(id, label) => {
          setForm((prev) => ({ ...prev, patientId: id }));
          setSelectedPatientName(label);
        }}
      />
    </>
  );
}

function createStyles(
  theme: ReturnType<typeof useAppTheme>['theme'],
  content: ReturnType<typeof useFormScrollPadding>,
) {
  return StyleSheet.create({
  content,
  selectedBox: {
    backgroundColor: withAlpha(theme.colors.brand, 0.08),
    borderRadius: theme.radius.md,
    padding: theme.spacing.md,
    borderWidth: 1,
    borderColor: withAlpha(theme.colors.brand, 0.18),
    gap: 4,
  },
  selectedLabel: {
    ...theme.typography.label,
    color: theme.colors.brand,
    textAlign: 'right',
  },
  selected: {
    ...theme.typography.subtitle,
    color: theme.colors.text,
    textAlign: 'right',
  },
  row: {
    flexDirection: 'row-reverse',
    gap: theme.spacing.md,
  },
  half: {
    flex: 1,
  },
  fieldLabel: {
    ...theme.typography.label,
    color: theme.colors.textMuted,
    textAlign: 'right',
  },
  errorBox: {
    backgroundColor: theme.colors.dangerSoft,
    borderRadius: theme.radius.md,
    padding: theme.spacing.md,
    borderWidth: 1,
    borderColor: theme.colors.danger,
  },
  error: {
    ...theme.typography.body,
    color: theme.colors.danger,
    textAlign: 'center',
  },
  });
}
