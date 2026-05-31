import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { useAppTheme } from '@/providers/ThemeProvider';

export function RevenueChart({
  data,
}: {
  data: Array<{ label: string; amount: number; manual: number; stripe: number }>;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const max = Math.max(1, ...data.map((row) => row.amount));

  return (
    <View style={styles.wrap}>
      <View style={styles.bars}>
        {data.map((row) => {
          const heightPct = Math.max(4, (row.amount / max) * 100);
          const manualPct = row.amount > 0 ? (row.manual / row.amount) * heightPct : 0;
          const stripePct = row.amount > 0 ? (row.stripe / row.amount) * heightPct : 0;

          return (
            <View key={row.label} style={styles.barCol}>
              <Text style={styles.amount}>{row.amount > 0 ? row.amount.toFixed(0) : ''}</Text>
              <View style={styles.barTrack}>
                {row.manual > 0 ? (
                  <View style={[styles.barManual, { height: `${manualPct}%` }]} />
                ) : null}
                {row.stripe > 0 ? (
                  <View style={[styles.barStripe, { height: `${stripePct}%` }]} />
                ) : null}
              </View>
              <Text style={styles.label} numberOfLines={1}>
                {row.label}
              </Text>
            </View>
          );
        })}
      </View>
      <View style={styles.legend}>
        <View style={styles.legendItem}>
          <View style={[styles.legendDot, styles.legendManual]} />
          <Text style={styles.legendText}>يدوي</Text>
        </View>
        <View style={styles.legendItem}>
          <View style={[styles.legendDot, styles.legendStripe]} />
          <Text style={styles.legendText}>Stripe</Text>
        </View>
      </View>
    </View>
  );
}

export function QuickActionGrid({
  actions,
}: {
  actions: Array<{
    label: string;
    icon: React.ComponentProps<typeof AppIcon>['name'];
    onPress: () => void;
    accent?: string;
  }>;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <View style={styles.actionsGrid}>
      {actions.map((action) => (
        <Pressable
          key={action.label}
          onPress={action.onPress}
          style={({ pressed }) => [styles.actionCard, pressed && styles.actionPressed]}
        >
          <View style={[styles.actionIcon, { backgroundColor: `${action.accent ?? theme.colors.brand}18` }]}>
            <AppIcon name={action.icon} size={22} color={action.accent ?? theme.colors.brand} />
          </View>
          <Text style={styles.actionLabel}>{action.label}</Text>
        </Pressable>
      ))}
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    wrap: {
      gap: theme.spacing.md,
    },
    bars: {
      flexDirection: 'row-reverse',
      alignItems: 'flex-end',
      justifyContent: 'space-between',
      gap: theme.spacing.sm,
      minHeight: 160,
    },
    barCol: {
      flex: 1,
      alignItems: 'center',
      gap: 6,
    },
    amount: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      fontSize: 10,
      minHeight: 14,
    },
    barTrack: {
      width: '100%',
      maxWidth: 36,
      height: 110,
      borderRadius: theme.radius.sm,
      backgroundColor: theme.colors.surfaceMuted,
      flexDirection: 'row-reverse',
      alignItems: 'flex-end',
      justifyContent: 'center',
      gap: 2,
      overflow: 'hidden',
      paddingHorizontal: 2,
      paddingBottom: 2,
    },
    barManual: {
      width: '42%',
      backgroundColor: theme.colors.success,
      borderTopLeftRadius: 4,
      borderTopRightRadius: 4,
      minHeight: 4,
    },
    barStripe: {
      width: '42%',
      backgroundColor: theme.colors.brand,
      borderTopLeftRadius: 4,
      borderTopRightRadius: 4,
      minHeight: 4,
    },
    label: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      fontSize: 10,
      textAlign: 'center',
    },
    legend: {
      flexDirection: 'row-reverse',
      justifyContent: 'center',
      gap: theme.spacing.lg,
    },
    legendItem: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: 6,
    },
    legendDot: {
      width: 10,
      height: 10,
      borderRadius: 5,
    },
    legendManual: {
      backgroundColor: theme.colors.success,
    },
    legendStripe: {
      backgroundColor: theme.colors.brand,
    },
    legendText: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
    },
    actionsGrid: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.sm,
      paddingHorizontal: theme.spacing.lg,
    },
    actionCard: {
      width: '48%',
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      borderWidth: 1,
      borderColor: theme.colors.border,
      padding: theme.spacing.md,
      alignItems: 'flex-end',
      gap: theme.spacing.sm,
      ...theme.shadows.sm,
    },
    actionPressed: {
      opacity: 0.92,
    },
    actionIcon: {
      width: 44,
      height: 44,
      borderRadius: theme.radius.md,
      alignItems: 'center',
      justifyContent: 'center',
    },
    actionLabel: {
      ...theme.typography.body,
      color: theme.colors.text,
      fontFamily: theme.fonts.semibold,
      textAlign: 'right',
    },
  });
}
