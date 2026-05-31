import { useEffect, useMemo, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchVisit, updateVisitClinical, updateVisitStatus } from '@/api/services/visits.service';
import { resolveClinicPersona } from '@/auth/permissions';
import { useAuth } from '@/auth/AuthContext';
import { FormSection } from '@/components/layout/FormSection';
import {
  FormScreen,
  FormScreenError,
  FormScreenLoading,
} from '@/components/layout/FormScreen';
import { Badge, Button, Input } from '@/components/ui/primitives';
import { useSemanticColors } from '@/hooks/useSemanticColors';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError, visitStatusLabels } from '@/utils/format';
import { fireHapticSuccess } from '@/utils/haptics';
import type { VisitStatus } from '@/types/api';

export default function VisitDetailScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const visitId = Number(id);
  const router = useRouter();
  const { user } = useAuth();
  const persona = resolveClinicPersona(user);
  const canEditClinical = persona === 'doctor' || persona === 'admin';
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const colors = useSemanticColors();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const query = useQuery({
    queryKey: queryKeys.visit(visitId),
    queryFn: () => fetchVisit(visitId),
    enabled: Number.isFinite(visitId) && visitId > 0,
  });

  const [chiefComplaint, setChiefComplaint] = useState('');
  const [diagnosis, setDiagnosis] = useState('');
  const [treatmentPlan, setTreatmentPlan] = useState('');
  const [notes, setNotes] = useState('');

  useEffect(() => {
    if (!query.data) {
      return;
    }
    setChiefComplaint(query.data.chief_complaint ?? '');
    setDiagnosis(query.data.diagnosis ?? '');
    setTreatmentPlan(query.data.treatment_plan ?? '');
    setNotes(query.data.notes ?? '');
  }, [query.data]);

  const saveMutation = useMutation({
    mutationFn: () =>
      updateVisitClinical(visitId, {
        chief_complaint: chiefComplaint.trim() || null,
        diagnosis: diagnosis.trim() || null,
        treatment_plan: treatmentPlan.trim() || null,
        notes: notes.trim() || null,
      }),
    onSuccess: async () => {
      fireHapticSuccess();
      toast.showSuccess('تم حفظ ملاحظات الزيارة');
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.visit(visitId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.visits() }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
      ]);
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  const statusMutation = useMutation({
    mutationFn: (status: VisitStatus) => updateVisitStatus(visitId, status),
    onSuccess: async (_data, status) => {
      toast.showSuccess(`تم تحديث الحالة: ${visitStatusLabels[status]}`);
      fireHapticSuccess();
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.visit(visitId) }),
        queryClient.invalidateQueries({ queryKey: queryKeys.visits() }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
      ]);
    },
    onError: (error) => toast.showError(formatApiError(error)),
  });

  if (query.isLoading) {
    return <FormScreenLoading title="ملاحظات الزيارة" />;
  }

  if (query.isError || !query.data) {
    return (
      <FormScreenError
        title="ملاحظات الزيارة"
        message={formatApiError(query.error)}
        onRetry={() => void query.refetch()}
      />
    );
  }

  const visit = query.data;
  const busy = saveMutation.isPending || statusMutation.isPending;
  const isClosed = visit.status === 'completed' || visit.status === 'cancelled';
  const statusColor = colors.visit[visit.status] ?? theme.colors.brand;

  return (
    <FormScreen title="ملاحظات الزيارة" subtitle={`#${visit.id}`}>
      <View style={styles.summary}>
        <Badge label={visitStatusLabels[visit.status] ?? visit.status} color={statusColor} />
        <Text style={styles.patient}>{visit.patient?.full_name ?? `#${visit.patient_id}`}</Text>
        <Text style={styles.meta}>الطبيب: {visit.doctor?.full_name ?? `#${visit.doctor_id}`}</Text>
        {visit.patient?.file_number ? (
          <Text style={styles.meta}>ملف: {visit.patient.file_number}</Text>
        ) : null}
      </View>

      {!isClosed && canEditClinical ? (
        <FormSection title="إجراءات سريعة">
          {visit.status === 'waiting' ? (
            <Button
              label="بدء الزيارة"
              icon="check"
              onPress={() => statusMutation.mutate('in_progress')}
              loading={statusMutation.isPending}
              disabled={busy}
            />
          ) : null}
          {visit.status === 'in_progress' ? (
            <Button
              label="إكمال الزيارة"
              icon="check"
              onPress={() => statusMutation.mutate('completed')}
              loading={statusMutation.isPending}
              disabled={busy}
            />
          ) : null}
        </FormSection>
      ) : null}

      {canEditClinical ? (
        <FormSection title="السجل السريري" subtitle="الشكوى، التشخيص، الخطة، والملاحظات">
          <Input
            label="الشكوى الرئيسية"
            value={chiefComplaint}
            onChangeText={setChiefComplaint}
            multiline
            numberOfLines={3}
            style={styles.fieldMultiline}
            editable={!isClosed}
          />
          <Input
            label="التشخيص"
            value={diagnosis}
            onChangeText={setDiagnosis}
            multiline
            numberOfLines={3}
            style={styles.fieldMultiline}
            editable={!isClosed}
          />
          <Input
            label="خطة العلاج"
            value={treatmentPlan}
            onChangeText={setTreatmentPlan}
            multiline
            numberOfLines={3}
            style={styles.fieldMultiline}
            editable={!isClosed}
          />
          <Input
            label="ملاحظات الطبيب"
            value={notes}
            onChangeText={setNotes}
            multiline
            numberOfLines={4}
            style={styles.fieldMultiline}
            editable={!isClosed}
          />
          {!isClosed ? (
            <Button
              label="حفظ الملاحظات"
              icon="check"
              onPress={() => saveMutation.mutate()}
              loading={saveMutation.isPending}
              disabled={busy}
            />
          ) : (
            <Text style={styles.closedHint}>الزيارة مغلقة — للعرض فقط</Text>
          )}
        </FormSection>
      ) : (
        <FormSection title="السجل السريري">
          {chiefComplaint ? <Text style={styles.readOnly}>الشكوى: {chiefComplaint}</Text> : null}
          {diagnosis ? <Text style={styles.readOnly}>التشخيص: {diagnosis}</Text> : null}
          {treatmentPlan ? <Text style={styles.readOnly}>الخطة: {treatmentPlan}</Text> : null}
          {notes ? <Text style={styles.readOnly}>ملاحظات: {notes}</Text> : null}
          {!chiefComplaint && !diagnosis && !treatmentPlan && !notes ? (
            <Text style={styles.closedHint}>لا توجد ملاحظات مسجّلة</Text>
          ) : null}
        </FormSection>
      )}

      <Button
        label="ملف المريض"
        variant="secondary"
        icon="user"
        onPress={() =>
          router.push({ pathname: '/(app)/patients/[id]', params: { id: String(visit.patient_id) } })
        }
      />
    </FormScreen>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    summary: { alignItems: 'flex-end', gap: 6 },
    patient: { ...theme.typography.title, color: theme.colors.text, textAlign: 'right' },
    meta: { ...theme.typography.caption, color: theme.colors.textMuted, textAlign: 'right' },
    fieldMultiline: { minHeight: 88, textAlignVertical: 'top' },
    closedHint: { ...theme.typography.body, color: theme.colors.textMuted, textAlign: 'center' },
    readOnly: { ...theme.typography.body, color: theme.colors.text, textAlign: 'right', marginBottom: 8 },
  });
}
