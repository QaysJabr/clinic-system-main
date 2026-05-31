import { useMemo, useState } from 'react';
import { Platform, Pressable, StyleSheet, Text, View } from 'react-native';
import DateTimePicker, { type DateTimePickerEvent } from '@react-native-community/datetimepicker';
import { AppIcon } from '@/components/ui/AppIcon';
import { useAppTheme } from '@/providers/ThemeProvider';

function parseDate(value: string): Date {
  const parsed = new Date(`${value}T12:00:00`);
  return Number.isNaN(parsed.getTime()) ? new Date() : parsed;
}

function parseTime(value: string): Date {
  const [hours, minutes] = value.split(':').map(Number);
  const date = new Date();
  date.setHours(Number.isFinite(hours) ? hours : 9, Number.isFinite(minutes) ? minutes : 0, 0, 0);
  return date;
}

function formatDateIso(date: Date): string {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

function formatTimeValue(date: Date): string {
  const h = String(date.getHours()).padStart(2, '0');
  const m = String(date.getMinutes()).padStart(2, '0');
  return `${h}:${m}`;
}

function formatDateLabel(value: string): string {
  const date = parseDate(value);
  return date.toLocaleDateString('ar', {
    weekday: 'short',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

function FieldShell({
  label,
  value,
  icon,
  onPress,
}: {
  label: string;
  value: string;
  icon: 'calendar' | 'clock';
  onPress: () => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <View style={styles.group}>
      <Text style={styles.label}>{label}</Text>
      <Pressable onPress={onPress} style={({ pressed }) => [styles.field, pressed && styles.fieldPressed]}>
        <Text style={styles.value}>{value}</Text>
        <AppIcon name={icon} size={18} color={theme.colors.brand} />
      </Pressable>
    </View>
  );
}

export function DateField({
  label,
  value,
  onChange,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
}) {
  const [show, setShow] = useState(false);

  function handleChange(event: DateTimePickerEvent, selected?: Date) {
    if (Platform.OS === 'android') {
      setShow(false);
    }
    if (event.type === 'dismissed' || !selected) {
      return;
    }
    onChange(formatDateIso(selected));
  }

  return (
    <>
      <FieldShell
        label={label}
        value={formatDateLabel(value)}
        icon="calendar"
        onPress={() => setShow(true)}
      />
      {show ? (
        <DateTimePicker
          value={parseDate(value)}
          mode="date"
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleChange}
        />
      ) : null}
    </>
  );
}

export function TimeField({
  label,
  value,
  onChange,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
}) {
  const [show, setShow] = useState(false);

  function handleChange(event: DateTimePickerEvent, selected?: Date) {
    if (Platform.OS === 'android') {
      setShow(false);
    }
    if (event.type === 'dismissed' || !selected) {
      return;
    }
    onChange(formatTimeValue(selected));
  }

  return (
    <>
      <FieldShell label={label} value={value} icon="clock" onPress={() => setShow(true)} />
      {show ? (
        <DateTimePicker
          value={parseTime(value)}
          mode="time"
          is24Hour
          display={Platform.OS === 'ios' ? 'spinner' : 'default'}
          onChange={handleChange}
        />
      ) : null}
    </>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    group: {
      gap: theme.spacing.xs,
    },
    label: {
      ...theme.typography.label,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    field: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      backgroundColor: theme.colors.surface,
      borderWidth: 1,
      borderColor: theme.colors.border,
      borderRadius: theme.radius.md,
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.md,
      ...theme.shadows.sm,
    },
    fieldPressed: {
      borderColor: theme.colors.brand,
      backgroundColor: theme.colors.surfaceMuted,
    },
    value: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
      flex: 1,
    },
  });
}
