import { useMemo } from 'react';
import { Alert, Pressable, StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { Badge, Button, Card } from '@/components/ui/primitives';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { PlatformClinic } from '@/types/api';

export function PlatformClinicCard({
  clinic,
  onPress,
  onActivate,
  onSuspend,
  activateLoading,
  suspendLoading,
  showQuickActions = false,
}: {
  clinic: PlatformClinic;
  onPress?: () => void;
  onActivate?: () => void;
  onSuspend?: () => void;
  activateLoading?: boolean;
  suspendLoading?: boolean;
  showQuickActions?: boolean;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const tierColors: Record<PlatformClinic['subscription_tier'], string> = {
    active: theme.colors.success,
    expiring: theme.colors.warning,
    expired: theme.colors.danger,
    suspended: theme.colors.textMuted,
  };
  const tierColor = tierColors[clinic.subscription_tier] ?? theme.colors.brand;

  function handleSuspend() {
    if (!onSuspend) {
      return;
    }
    Alert.alert('تعليق العيادة', `تعليق "${clinic.name}"؟`, [
      { text: 'إلغاء', style: 'cancel' },
      { text: 'تعليق', style: 'destructive', onPress: onSuspend },
    ]);
  }

  const content = (
    <>
      <View style={styles.header}>
        <View style={styles.badges}>
          <Badge label={clinic.subscription_tier_label} color={tierColor} />
          <Badge
            label={clinic.is_active ? 'حساب نشط' : 'معلّقة'}
            color={clinic.is_active ? theme.colors.success : theme.colors.textMuted}
          />
        </View>
        <Text style={styles.id}>#{clinic.id}</Text>
      </View>
      <Text style={styles.name}>{clinic.name}</Text>
      {clinic.owner ? (
        <View style={styles.metaRow}>
          <AppIcon name="mail" size={15} color={theme.colors.textMuted} />
          <Text style={styles.meta}>{clinic.owner.email}</Text>
        </View>
      ) : null}
      {clinic.plan?.name ? (
        <View style={styles.metaRow}>
          <AppIcon name="file-text" size={15} color={theme.colors.textMuted} />
          <Text style={styles.meta}>{clinic.plan.name}</Text>
        </View>
      ) : null}
      {clinic.total_paid != null ? (
        <Text style={styles.paid}>إجمالي المدفوعات: {clinic.total_paid.toFixed(2)}</Text>
      ) : null}
      {clinic.subscription_expires_at ? (
        <Text style={styles.expires}>ينتهي: {clinic.subscription_expires_at.slice(0, 10)}</Text>
      ) : null}
      {showQuickActions && (onActivate || onSuspend || onPress) ? (
        <View style={styles.actions}>
          {onPress ? (
            <Button label="تفاصيل" variant="secondary" compact onPress={onPress} />
          ) : null}
          {onActivate ? (
            <Button
              label="تفعيل"
              icon="check"
              compact
              onPress={onActivate}
              loading={activateLoading}
              disabled={activateLoading || suspendLoading}
            />
          ) : null}
          {onSuspend ? (
            <Button
              label="تعليق"
              variant="danger"
              icon="x"
              compact
              onPress={handleSuspend}
              loading={suspendLoading}
              disabled={activateLoading || suspendLoading}
            />
          ) : null}
        </View>
      ) : null}
    </>
  );

  if (onPress && !showQuickActions) {
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
      alignItems: 'flex-start',
      marginBottom: theme.spacing.sm,
      gap: theme.spacing.sm,
    },
    badges: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.xs,
      flex: 1,
    },
    id: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
    },
    name: {
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
      flex: 1,
    },
    paid: {
      ...theme.typography.caption,
      color: theme.colors.brand,
      textAlign: 'right',
      marginTop: theme.spacing.sm,
      fontWeight: '600',
    },
    expires: {
      ...theme.typography.caption,
      color: theme.colors.warning,
      textAlign: 'right',
      marginTop: theme.spacing.xs,
    },
    actions: {
      flexDirection: 'row-reverse',
      gap: theme.spacing.sm,
      marginTop: theme.spacing.md,
    },
    pressed: {
      opacity: 0.92,
    },
  });
}
