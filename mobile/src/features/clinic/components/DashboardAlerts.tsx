import { useMemo } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { DashboardAlert } from '@/types/api';

const iconByType = {
  warning: 'alert-circle',
  info: 'alert-circle',
  danger: 'x',
} as const;

export function DashboardAlerts({ alerts }: { alerts: DashboardAlert[] }) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  if (alerts.length === 0) {
    return null;
  }

  return (
    <View style={styles.wrap}>
      {alerts.map((alert, index) => {
        const type = alert.type ?? 'info';
        const color =
          type === 'warning' ? theme.colors.warning : type === 'danger' ? theme.colors.danger : theme.colors.info;

        return (
          <View key={`${alert.title}-${index}`} style={[styles.card, { borderRightColor: color }]}>
            <View style={styles.row}>
              <AppIcon name={iconByType[type] ?? 'info'} size={18} color={color} />
              <View style={styles.text}>
                <Text style={styles.title}>{alert.title}</Text>
                {alert.message ? <Text style={styles.message}>{alert.message}</Text> : null}
              </View>
            </View>
          </View>
        );
      })}
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    wrap: {
      paddingHorizontal: theme.spacing.lg,
      gap: theme.spacing.sm,
    },
    card: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      padding: theme.spacing.md,
      borderRightWidth: 4,
      ...theme.shadows.sm,
    },
    row: {
      flexDirection: 'row-reverse',
      alignItems: 'flex-start',
      gap: theme.spacing.sm,
    },
    text: { flex: 1, alignItems: 'flex-end' },
    title: { ...theme.typography.subtitle, color: theme.colors.text, textAlign: 'right' },
    message: { ...theme.typography.caption, color: theme.colors.textMuted, textAlign: 'right', marginTop: 4 },
  });
}
