import { Pressable, StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { Badge, Button, Card } from '@/components/ui/primitives';
import { canCheckInAppointment } from '@/auth/permissions';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useSemanticColors } from '@/hooks/useSemanticColors';
import type { Appointment } from '@/types/api';
import { appointmentStatusLabels } from '@/utils/format';
import { useMemo } from 'react';

export function AppointmentCard({
  appointment,
  onPress,
  onPatientPress,
  onCheckIn,
  checkInLoading,
}: {
  appointment: Appointment;
  onPress?: () => void;
  onPatientPress?: (patientId: number) => void;
  onCheckIn?: () => void;
  checkInLoading?: boolean;
}) {
  const { theme } = useAppTheme();
  const colors = useSemanticColors();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const patientName = appointment.patient?.full_name ?? `#${appointment.patient_id}`;
  const doctorName = appointment.doctor?.full_name ?? `#${appointment.doctor_id}`;
  const statusLabel = appointmentStatusLabels[appointment.status] ?? appointment.status;
  const statusColor = colors.appointment[appointment.status] ?? theme.colors.brand;
  const showCheckIn = onCheckIn && canCheckInAppointment(appointment.status, appointment.visit_id);

  const patientRow = onPatientPress ? (
    <Pressable
      onPress={() => onPatientPress(appointment.patient_id)}
      style={({ pressed }) => [styles.patientPress, pressed && styles.pressed]}
    >
      <Text style={styles.patient}>{patientName}</Text>
      <AppIcon name="chevron-left" size={16} color={theme.colors.brand} />
    </Pressable>
  ) : (
    <Text style={styles.patient}>{patientName}</Text>
  );

  const content = (
    <>
      <View style={styles.header}>
        <Badge label={statusLabel} color={statusColor} />
        <View style={styles.timeRow}>
          <AppIcon name="clock" size={16} color={theme.colors.brand} />
          <Text style={styles.time}>
            {appointment.start_time} – {appointment.end_time}
          </Text>
        </View>
      </View>
      {patientRow}
      <View style={styles.metaRow}>
        <AppIcon name="stethoscope" size={15} color={theme.colors.textMuted} />
        <Text style={styles.meta}>{doctorName}</Text>
      </View>
      {appointment.reason ? <Text style={styles.reason}>{appointment.reason}</Text> : null}
      {showCheckIn ? (
        <View style={styles.actions}>
          <Button label="تسجيل حضور" icon="check" compact loading={checkInLoading} onPress={onCheckIn} />
        </View>
      ) : null}
    </>
  );

  if (onPress) {
    return (
      <Pressable onPress={onPress} style={({ pressed }) => [pressed && styles.pressed]}>
        <Card>{content}</Card>
      </Pressable>
    );
  }

  return <Card>{content}</Card>;
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    header: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: theme.spacing.sm,
      gap: theme.spacing.sm,
    },
    timeRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: 6,
    },
    time: {
      ...theme.typography.subtitle,
      color: theme.colors.brand,
    },
    patientPress: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'flex-end',
      gap: 4,
    },
    patient: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      textAlign: 'right',
    },
    metaRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: 6,
      marginTop: 6,
    },
    meta: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    reason: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
      marginTop: theme.spacing.sm,
      paddingTop: theme.spacing.sm,
      borderTopWidth: 1,
      borderTopColor: theme.colors.border,
    },
    actions: {
      marginTop: theme.spacing.md,
    },
    pressed: {
      opacity: 0.92,
    },
  });
}
