import { useMemo } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useLocalSearchParams } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { fetchPatient } from '@/api/services/patients.service';
import {
  FormScreen,
  FormScreenError,
  FormScreenLoading,
} from '@/components/layout/FormScreen';
import { AppIcon } from '@/components/ui/AppIcon';
import { Card } from '@/components/ui/primitives';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { formatApiError, formatGender } from '@/utils/format';

function InfoRow({
  icon,
  label,
  value,
}: {
  icon: React.ComponentProps<typeof AppIcon>['name'];
  label: string;
  value: string;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createInfoStyles(theme), [theme]);

  return (
    <View style={styles.row}>
      <View style={styles.iconWrap}>
        <AppIcon name={icon} size={18} color={theme.colors.brand} />
      </View>
      <View style={styles.textWrap}>
        <Text style={styles.label}>{label}</Text>
        <Text style={styles.value}>{value}</Text>
      </View>
    </View>
  );
}

export default function PatientProfileScreen() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const patientId = Number(id);
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const query = useQuery({
    queryKey: queryKeys.patient(patientId),
    queryFn: () => fetchPatient(patientId),
    enabled: Number.isFinite(patientId) && patientId > 0,
  });

  if (query.isLoading) {
    return <FormScreenLoading title="ملف المريض" />;
  }

  if (query.isError || !query.data) {
    return (
      <FormScreenError
        title="ملف المريض"
        message={formatApiError(query.error, 'تعذر تحميل بيانات المريض')}
        onRetry={() => void query.refetch()}
      />
    );
  }

  const patient = query.data;

  return (
    <FormScreen title="ملف المريض" subtitle={patient.file_number ?? `#${patient.id}`} contentStyle={styles.content}>
      <LinearGradient
        colors={[theme.colors.brand, theme.colors.brandSecondary]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.hero}
      >
        <View style={styles.avatarRing}>
          <View style={styles.avatar}>
            <AppIcon name="user" size={36} color={theme.colors.brand} />
          </View>
        </View>
        <Text style={styles.name}>{patient.full_name}</Text>
        <Text style={styles.heroMeta}>
          {patient.file_number ? `ملف ${patient.file_number}` : 'مريض مسجل في العيادة'}
        </Text>
      </LinearGradient>

      <Card>
        <Text style={styles.sectionTitle}>معلومات الاتصال</Text>
        <InfoRow icon="phone" label="الهاتف" value={patient.phone?.trim() || 'غير مسجل'} />
        <InfoRow icon="mail" label="البريد الإلكتروني" value={patient.email?.trim() || 'غير مسجل'} />
      </Card>

      <Card>
        <Text style={styles.sectionTitle}>البيانات الشخصية</Text>
        <InfoRow icon="calendar" label="تاريخ الميلاد" value={patient.date_of_birth ?? 'غير مسجل'} />
        <InfoRow icon="user" label="الجنس" value={formatGender(patient.gender)} />
      </Card>

      {patient.notes ? (
        <Card>
          <Text style={styles.sectionTitle}>ملاحظات</Text>
          <Text style={styles.notes}>{patient.notes}</Text>
        </Card>
      ) : null}
    </FormScreen>
  );
}

function createInfoStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    row: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
    },
    iconWrap: {
      width: 40,
      height: 40,
      borderRadius: theme.radius.md,
      backgroundColor: theme.colors.surfaceMuted,
      alignItems: 'center',
      justifyContent: 'center',
    },
    textWrap: {
      flex: 1,
      alignItems: 'flex-end',
    },
    label: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
    },
    value: {
      ...theme.typography.body,
      color: theme.colors.text,
      marginTop: 2,
    },
  });
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    content: {
      paddingTop: 0,
    },
    hero: {
      alignItems: 'center',
      paddingVertical: theme.spacing.xl,
      paddingHorizontal: theme.spacing.lg,
      borderRadius: theme.radius.xl,
      gap: theme.spacing.sm,
      marginBottom: theme.spacing.sm,
      ...theme.shadows.md,
    },
    avatarRing: {
      padding: 3,
      borderRadius: 999,
      backgroundColor: 'rgba(255,255,255,0.25)',
    },
    avatar: {
      width: 72,
      height: 72,
      borderRadius: 36,
      backgroundColor: theme.colors.textInverse,
      alignItems: 'center',
      justifyContent: 'center',
    },
    name: {
      ...theme.typography.title,
      color: theme.colors.textInverse,
      textAlign: 'center',
    },
    heroMeta: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.85)',
    },
    sectionTitle: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      textAlign: 'right',
      marginBottom: theme.spacing.sm,
    },
    notes: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
      lineHeight: 22,
    },
  });
}
