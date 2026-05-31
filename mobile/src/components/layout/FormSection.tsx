import { useMemo } from 'react';
import { StyleSheet, Text, View, type ViewProps } from 'react-native';
import { useAppTheme } from '@/providers/ThemeProvider';

export function FormSection({
  title,
  subtitle,
  children,
  style,
}: ViewProps & {
  title: string;
  subtitle?: string;
  children: React.ReactNode;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <View style={[styles.section, style]}>
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
      </View>
      <View style={styles.body}>{children}</View>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    section: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      borderWidth: 1,
      borderColor: theme.colors.border,
      overflow: 'hidden',
      ...theme.shadows.sm,
    },
    header: {
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      backgroundColor: theme.colors.surfaceMuted,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
      gap: 4,
    },
    title: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      textAlign: 'right',
    },
    subtitle: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    body: {
      padding: theme.spacing.lg,
      gap: theme.spacing.md,
    },
  });
}
