import { useMemo } from 'react';
import { ActivityIndicator, StyleSheet, Text, View, type ViewProps } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { Button } from '@/components/ui/primitives';
import { useAppTheme } from '@/providers/ThemeProvider';

export function ScreenContainer({ children, style, ...rest }: ViewProps) {
  const { theme } = useAppTheme();
  return (
    <View style={[{ flex: 1, backgroundColor: theme.colors.background }, style]} {...rest}>
      {children}
    </View>
  );
}

export function LoadingView({ label = 'جاري التحميل...' }: { label?: string }) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createCenteredStyles(theme), [theme]);

  return (
    <View style={styles.centered}>
      <View style={styles.iconCircle}>
        <ActivityIndicator size="large" color={theme.colors.brand} />
      </View>
      <Text style={styles.loadingText}>{label}</Text>
    </View>
  );
}

export function ErrorView({
  message,
  onRetry,
}: {
  message: string;
  onRetry?: () => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createCenteredStyles(theme), [theme]);

  return (
    <View style={styles.centered}>
      <View style={[styles.iconCircle, styles.iconCircleDanger]}>
        <AppIcon name="alert-circle" size={28} color={theme.colors.danger} />
      </View>
      <Text style={styles.errorTitle}>تعذر تحميل البيانات</Text>
      <Text style={styles.errorMessage}>{message}</Text>
      {onRetry ? <Button label="إعادة المحاولة" icon="refresh-cw" onPress={onRetry} /> : null}
    </View>
  );
}

export function EmptyState({
  title,
  subtitle,
  actionLabel,
  onAction,
}: {
  title: string;
  subtitle?: string;
  actionLabel?: string;
  onAction?: () => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createCenteredStyles(theme), [theme]);

  return (
    <View style={styles.centered}>
      <View style={styles.iconCircle}>
        <AppIcon name="inbox" size={28} color={theme.colors.brand} />
      </View>
      <Text style={styles.errorTitle}>{title}</Text>
      {subtitle ? <Text style={styles.errorMessage}>{subtitle}</Text> : null}
      {actionLabel && onAction ? <Button label={actionLabel} icon="plus" onPress={onAction} /> : null}
    </View>
  );
}

function createCenteredStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    centered: {
      flex: 1,
      alignItems: 'center',
      justifyContent: 'center',
      padding: theme.spacing.xl,
      gap: theme.spacing.md,
      backgroundColor: theme.colors.background,
    },
    iconCircle: {
      width: 72,
      height: 72,
      borderRadius: 36,
      backgroundColor: theme.colors.surface,
      borderWidth: 1,
      borderColor: theme.colors.border,
      alignItems: 'center',
      justifyContent: 'center',
      ...theme.shadows.sm,
    },
    iconCircleDanger: {
      backgroundColor: theme.colors.dangerSoft,
      borderColor: theme.colors.danger,
    },
    loadingText: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
    },
    errorTitle: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
      textAlign: 'center',
    },
    errorMessage: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
      textAlign: 'center',
    },
  });
}
