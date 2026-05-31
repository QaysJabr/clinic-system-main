import { useMemo, type ComponentProps } from 'react';
import { StyleSheet, Text, View, type ViewProps } from 'react-native';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { Button } from '@/components/ui/primitives';
import { useAppTheme } from '@/providers/ThemeProvider';

export function Panel({
  title,
  actionLabel,
  onAction,
  children,
  style,
}: ViewProps & {
  title: string;
  actionLabel?: string;
  onAction?: () => void;
  children: React.ReactNode;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createPanelStyles(theme), [theme]);

  return (
    <View style={[styles.panel, style]}>
      <View style={styles.header}>
        <Text style={styles.title}>{title}</Text>
        {actionLabel && onAction ? (
          <Button label={actionLabel} variant="ghost" onPress={onAction} compact />
        ) : null}
      </View>
      <View style={styles.body}>{children}</View>
    </View>
  );
}

export function SectionHeader({ title, subtitle }: { title: string; subtitle?: string }) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createPanelStyles(theme), [theme]);

  return (
    <View style={styles.sectionHeader}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {subtitle ? <Text style={styles.sectionSubtitle}>{subtitle}</Text> : null}
    </View>
  );
}

export function ListScreenHeader({
  title,
  subtitle,
  actionLabel,
  onAction,
  actionIcon = 'plus',
}: {
  title: string;
  subtitle?: string;
  actionLabel?: string;
  onAction?: () => void;
  actionIcon?: ComponentProps<typeof Button>['icon'];
}) {
  const { theme } = useAppTheme();
  const { top } = useScreenInsets();
  const styles = useMemo(() => createPanelStyles(theme, top), [theme, top]);

  return (
    <View style={styles.listHeader}>
      <View style={styles.listHeaderText}>
        <Text style={styles.listTitle}>{title}</Text>
        {subtitle ? <Text style={styles.listSubtitle}>{subtitle}</Text> : null}
      </View>
      {actionLabel && onAction ? (
        <Button label={actionLabel} onPress={onAction} icon={actionIcon} compact />
      ) : null}
    </View>
  );
}

function createPanelStyles(theme: ReturnType<typeof useAppTheme>['theme'], topInset = 0) {
  return StyleSheet.create({
    panel: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.xl,
      borderWidth: 1,
      borderColor: theme.colors.border,
      overflow: 'hidden',
      marginHorizontal: theme.spacing.lg,
      ...theme.shadows.sm,
    },
    header: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      backgroundColor: theme.colors.surfaceMuted,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
    },
    title: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
    },
    body: {
      padding: theme.spacing.lg,
      gap: theme.spacing.md,
    },
    sectionHeader: {
      gap: 4,
      marginBottom: theme.spacing.sm,
    },
    sectionTitle: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      textAlign: 'right',
    },
    sectionSubtitle: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    listHeader: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      paddingHorizontal: theme.spacing.lg,
      paddingTop: topInset + theme.spacing.md,
      paddingBottom: theme.spacing.sm,
      gap: theme.spacing.md,
    },
    listHeaderText: {
      flex: 1,
      alignItems: 'flex-end',
      gap: 4,
    },
    listTitle: {
      ...theme.typography.title,
      color: theme.colors.text,
    },
    listSubtitle: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
    },
  });
}
