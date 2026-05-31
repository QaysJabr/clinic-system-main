import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { Badge, Card } from '@/components/ui/primitives';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { Visit } from '@/types/api';
import { visitStatusLabels } from '@/utils/format';

export function VisitQueueCard({
  visit,
  onPress,
}: {
  visit: Visit;
  onPress?: () => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const visitColors: Record<string, string> = {
    waiting: theme.colors.warning,
    in_progress: theme.colors.info,
    completed: theme.colors.success,
    cancelled: theme.colors.textMuted,
  };

  const patientName = visit.patient?.full_name ?? `#${visit.patient_id}`;
  const statusLabel = visitStatusLabels[visit.status] ?? visit.status;

  const content = (
    <>
      <View style={styles.row}>
        <Badge label={statusLabel} color={visitColors[visit.status] ?? theme.colors.brand} />
        <Text style={styles.patient}>{patientName}</Text>
      </View>
      {visit.chief_complaint ? <Text style={styles.complaint}>{visit.chief_complaint}</Text> : null}
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
    row: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    patient: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      flex: 1,
      textAlign: 'right',
    },
    complaint: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
      marginTop: theme.spacing.sm,
    },
    pressed: {
      opacity: 0.92,
    },
  });
}
