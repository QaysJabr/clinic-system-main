import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { Badge, Button, Card } from '@/components/ui/primitives';
import { withAlpha } from '@/config/theme';
import { useSemanticColors } from '@/hooks/useSemanticColors';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { Visit, VisitStatus } from '@/types/api';
import { visitStatusLabels } from '@/utils/format';

const nextStatus: Partial<Record<VisitStatus, VisitStatus>> = {
  waiting: 'in_progress',
  in_progress: 'completed',
};

const statusSteps: VisitStatus[] = ['waiting', 'in_progress', 'completed'];

function stepIndex(status: VisitStatus): number {
  return statusSteps.indexOf(status);
}

export function VisitCard({
  visit,
  onStatusChange,
  onPatientPress,
  onOpenDetail,
  loading,
}: {
  visit: Visit;
  onStatusChange: (status: VisitStatus) => void;
  onPatientPress?: (patientId: number) => void;
  onOpenDetail?: () => void;
  loading?: boolean;
}) {
  const { theme } = useAppTheme();
  const colors = useSemanticColors();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const patientName = visit.patient?.full_name ?? `#${visit.patient_id}`;
  const doctorName = visit.doctor?.full_name ?? `#${visit.doctor_id}`;
  const statusLabel = visitStatusLabels[visit.status] ?? visit.status;
  const next = nextStatus[visit.status];
  const currentStep = stepIndex(visit.status);
  const isClosed = visit.status === 'completed' || visit.status === 'cancelled';

  return (
    <Card>
      <View style={styles.header}>
        <Badge label={statusLabel} color={colors.visit[visit.status]} />
        {onPatientPress ? (
          <Pressable
            onPress={() => onPatientPress(visit.patient_id)}
            style={({ pressed }) => [styles.patientPress, pressed && styles.pressed]}
          >
            <Text style={styles.patient}>{patientName}</Text>
            <AppIcon name="chevron-left" size={16} color={theme.colors.brand} />
          </Pressable>
        ) : (
          <Text style={styles.patient}>{patientName}</Text>
        )}
      </View>

      {!isClosed ? (
        <View style={styles.steps}>
          {statusSteps.map((step, index) => {
            const active = currentStep >= index && visit.status !== 'cancelled';
            return (
              <View key={step} style={styles.stepItem}>
                <View style={[styles.stepDot, active && styles.stepDotActive]} />
                {index < statusSteps.length - 1 ? (
                  <View style={[styles.stepLine, active && styles.stepLineActive]} />
                ) : null}
              </View>
            );
          })}
        </View>
      ) : null}

      <View style={styles.metaRow}>
        <AppIcon name="stethoscope" size={15} color={theme.colors.textMuted} />
        <Text style={styles.meta}>{doctorName}</Text>
      </View>
      {visit.patient?.file_number ? (
        <View style={styles.metaRow}>
          <AppIcon name="file-text" size={15} color={theme.colors.textMuted} />
          <Text style={styles.meta}>ملف: {visit.patient.file_number}</Text>
        </View>
      ) : null}
      {visit.chief_complaint ? (
        <View style={styles.complaintBox}>
          <Text style={styles.complaint}>{visit.chief_complaint}</Text>
        </View>
      ) : null}

      {!isClosed ? (
        <View style={styles.actions}>
          {onOpenDetail ? (
            <Button label="ملاحظات الزيارة" variant="secondary" icon="file-text" onPress={onOpenDetail} disabled={loading} />
          ) : null}
          {next ? (
            <Button
              label={visit.status === 'waiting' ? 'بدء الزيارة' : 'إكمال الزيارة'}
              onPress={() => onStatusChange(next)}
              loading={loading}
            />
          ) : null}
          <Button
            label="إلغاء الزيارة"
            variant="danger"
            icon="x"
            onPress={() => onStatusChange('cancelled')}
            disabled={loading}
          />
        </View>
      ) : null}
    </Card>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    header: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'center',
      gap: theme.spacing.sm,
      marginBottom: theme.spacing.sm,
    },
    patient: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      flex: 1,
      textAlign: 'right',
    },
    patientPress: {
      flex: 1,
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'flex-end',
      gap: 4,
    },
    pressed: {
      opacity: 0.92,
    },
    steps: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'center',
      gap: 0,
      marginBottom: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
    },
    stepItem: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
    },
    stepDot: {
      width: 12,
      height: 12,
      borderRadius: 6,
      backgroundColor: theme.colors.borderStrong,
    },
    stepDotActive: {
      backgroundColor: theme.colors.brand,
    },
    stepLine: {
      width: 42,
      height: 2,
      backgroundColor: theme.colors.border,
      marginHorizontal: 4,
    },
    stepLineActive: {
      backgroundColor: withAlpha(theme.colors.brand, 0.45),
    },
    metaRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: 6,
      marginTop: 4,
    },
    meta: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    complaintBox: {
      marginTop: theme.spacing.md,
      backgroundColor: theme.colors.surfaceMuted,
      borderRadius: theme.radius.md,
      padding: theme.spacing.md,
    },
    complaint: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
    },
    actions: {
      marginTop: theme.spacing.lg,
      gap: theme.spacing.sm,
    },
  });
}
