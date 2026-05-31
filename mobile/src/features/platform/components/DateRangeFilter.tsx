import { useMemo } from 'react';
import { StyleSheet, View } from 'react-native';
import { DateField, TimeField } from '@/components/ui/DateTimeField';
import { FormSection } from '@/components/layout/FormSection';
import { useAppTheme } from '@/providers/ThemeProvider';

export function DateRangeFilter({
  title = 'فلتر التاريخ',
  subtitle,
  paidFrom,
  paidTo,
  onPaidFromChange,
  onPaidToChange,
}: {
  title?: string;
  subtitle?: string;
  paidFrom: string;
  paidTo: string;
  onPaidFromChange: (value: string) => void;
  onPaidToChange: (value: string) => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <FormSection title={title} subtitle={subtitle}>
      <View style={styles.row}>
        <View style={styles.field}>
          <DateField label="من تاريخ" value={paidFrom} onChange={onPaidFromChange} />
        </View>
        <View style={styles.field}>
          <DateField label="إلى تاريخ" value={paidTo} onChange={onPaidToChange} />
        </View>
      </View>
    </FormSection>
  );
}

export function splitIsoDateTime(iso: string | null | undefined): { date: string; time: string } {
  if (!iso) {
    const now = new Date();
    const date = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
    return { date, time: '09:00' };
  }

  const normalized = iso.includes('T') ? iso : iso.replace(' ', 'T');
  const [datePart, timePart = '09:00:00'] = normalized.split('T');
  const [hours, minutes] = timePart.split(':');
  return {
    date: datePart,
    time: `${hours ?? '09'}:${minutes ?? '00'}`,
  };
}

export function combineIsoDateTime(date: string, time: string): string {
  if (!date.trim()) {
    return '';
  }
  const safeTime = time.trim() || '09:00';
  return `${date}T${safeTime}:00`;
}

export function DateTimeField({
  label,
  value,
  onChange,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
}) {
  const { date, time } = splitIsoDateTime(value);

  return (
    <View>
      <DateField
        label={label}
        value={date}
        onChange={(nextDate) => onChange(combineIsoDateTime(nextDate, time))}
      />
      <TimeField
        label={`${label} — الوقت`}
        value={time}
        onChange={(nextTime) => onChange(combineIsoDateTime(date, nextTime))}
      />
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    row: {
      flexDirection: 'row-reverse',
      gap: theme.spacing.md,
    },
    field: {
      flex: 1,
    },
  });
}
