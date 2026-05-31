import { useMemo } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';

export function ChipGroup<T extends string>({
  options,
  value,
  onChange,
  labels,
}: {
  options: T[];
  value: T;
  onChange: (value: T) => void;
  labels: Record<T, string>;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.row}>
      {options.map((option) => {
        const active = option === value;
        return (
          <Pressable
            key={option}
            onPress={() => onChange(option)}
            style={[styles.chip, active && styles.chipActive]}
          >
            <Text style={[styles.chipText, active && styles.chipTextActive]}>{labels[option]}</Text>
          </Pressable>
        );
      })}
    </ScrollView>
  );
}

export function OptionList({
  items,
  selectedId,
  onSelect,
  emptyLabel = 'لا توجد نتائج',
}: {
  items: { id: number; label: string; sublabel?: string }[];
  selectedId?: number | null;
  onSelect: (id: number) => void;
  emptyLabel?: string;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  if (items.length === 0) {
    return <Text style={styles.empty}>{emptyLabel}</Text>;
  }

  return (
    <View style={styles.list}>
      {items.map((item) => {
        const active = item.id === selectedId;
        return (
          <Pressable
            key={item.id}
            onPress={() => onSelect(item.id)}
            style={[styles.option, active && styles.optionActive]}
          >
            <Text style={[styles.optionLabel, active && styles.optionLabelActive]}>{item.label}</Text>
            {item.sublabel ? <Text style={styles.optionSub}>{item.sublabel}</Text> : null}
          </Pressable>
        );
      })}
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    row: {
      flexDirection: 'row-reverse',
      gap: theme.spacing.sm,
      paddingVertical: theme.spacing.xs,
      paddingHorizontal: theme.spacing.lg,
    },
    chip: {
      borderRadius: theme.radius.pill,
      borderWidth: 1,
      borderColor: theme.colors.border,
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.sm,
      backgroundColor: theme.colors.surface,
    },
    chipActive: {
      backgroundColor: theme.colors.brand,
      borderColor: theme.colors.brand,
      ...theme.shadows.sm,
    },
    chipText: {
      ...theme.typography.caption,
      color: theme.colors.text,
      fontFamily: theme.fonts.semibold,
    },
    chipTextActive: {
      color: theme.colors.textInverse,
    },
    list: {
      gap: theme.spacing.sm,
    },
    option: {
      borderWidth: 1,
      borderColor: theme.colors.border,
      borderRadius: theme.radius.md,
      padding: theme.spacing.md,
      backgroundColor: theme.colors.surface,
      ...theme.shadows.sm,
    },
    optionActive: {
      borderColor: theme.colors.brand,
      backgroundColor: withAlpha(theme.colors.brand, 0.08),
    },
    optionLabel: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
    },
    optionLabelActive: {
      color: theme.colors.brand,
      fontFamily: theme.fonts.bold,
    },
    optionSub: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
      marginTop: 2,
    },
    empty: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'center',
      paddingVertical: theme.spacing.md,
    },
  });
}
