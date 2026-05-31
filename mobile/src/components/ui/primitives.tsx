import { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
  type PressableProps,
  type TextInputProps,
} from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import type { ComponentProps } from 'react';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';
import { fireHapticLight } from '@/utils/haptics';

export function Card({
  children,
  elevated = true,
}: {
  children: React.ReactNode;
  elevated?: boolean;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  return <View style={[styles.card, elevated && theme.shadows.sm]}>{children}</View>;
}

export function Button({
  label,
  loading,
  variant = 'primary',
  icon,
  compact = false,
  onPress,
  ...rest
}: PressableProps & {
  label: string;
  loading?: boolean;
  variant?: 'primary' | 'ghost' | 'danger' | 'secondary' | 'inverse';
  icon?: ComponentProps<typeof AppIcon>['name'];
  compact?: boolean;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const isPrimary = variant === 'primary';
  const isDanger = variant === 'danger';
  const isSecondary = variant === 'secondary';
  const isInverse = variant === 'inverse';

  return (
    <Pressable
      style={({ pressed }) => [
        styles.button,
        compact && styles.buttonCompact,
        isPrimary && styles.buttonPrimary,
        variant === 'ghost' && styles.buttonGhost,
        isDanger && styles.buttonDanger,
        isSecondary && styles.buttonSecondary,
        isInverse && styles.buttonInverse,
        pressed && styles.buttonPressed,
        rest.disabled && styles.buttonDisabled,
        isPrimary && theme.shadows.sm,
      ]}
      onPress={(event) => {
        if (!rest.disabled && !loading) {
          fireHapticLight();
        }
        onPress?.(event);
      }}
      {...rest}
    >
      {loading ? (
        <ActivityIndicator color={isPrimary || isDanger ? theme.colors.textInverse : theme.colors.brand} />
      ) : (
        <View style={styles.buttonContent}>
          {icon ? (
            <AppIcon
              name={icon}
              size={18}
              color={
                isPrimary || isDanger
                  ? theme.colors.textInverse
                  : isInverse
                    ? theme.colors.textInverse
                    : isSecondary
                      ? theme.colors.text
                      : theme.colors.brand
              }
            />
          ) : null}
          <Text
            style={[
              styles.buttonText,
              compact && styles.buttonTextCompact,
              (isPrimary || isDanger) && styles.buttonTextInverse,
              variant === 'ghost' && styles.buttonTextGhost,
              isSecondary && styles.buttonTextSecondary,
              isInverse && styles.buttonTextInverse,
            ]}
          >
            {label}
          </Text>
        </View>
      )}
    </Pressable>
  );
}

export function Input({
  label,
  error,
  icon,
  secureToggle,
  ...rest
}: TextInputProps & {
  label: string;
  error?: string;
  icon?: ComponentProps<typeof AppIcon>['name'];
  secureToggle?: boolean;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [passwordVisible, setPasswordVisible] = useState(false);
  const isSecure = Boolean(rest.secureTextEntry) && secureToggle && !passwordVisible;

  return (
    <View style={styles.inputGroup}>
      <Text style={styles.inputLabel}>{label}</Text>
      <View style={[styles.inputWrap, error ? styles.inputWrapError : null]}>
        {icon ? (
          <View style={styles.inputIcon}>
            <AppIcon name={icon} size={18} color={theme.colors.textMuted} />
          </View>
        ) : null}
        <TextInput
          placeholderTextColor={theme.colors.textSubtle}
          style={styles.input}
          {...rest}
          secureTextEntry={isSecure}
        />
        {secureToggle && rest.secureTextEntry ? (
          <Pressable
            onPress={() => setPasswordVisible((value) => !value)}
            style={styles.inputTrailing}
            hitSlop={8}
          >
            <AppIcon
              name={passwordVisible ? 'eye-off' : 'eye'}
              size={18}
              color={theme.colors.textMuted}
            />
          </Pressable>
        ) : null}
      </View>
      {error ? <Text style={styles.inputErrorText}>{error}</Text> : null}
    </View>
  );
}

export function StatCard({
  label,
  value,
  accent,
  icon,
  hint,
}: {
  label: string;
  value: number | string;
  accent?: string;
  icon?: ComponentProps<typeof AppIcon>['name'];
  hint?: string;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const color = accent ?? theme.colors.brand;

  return (
    <View style={[styles.statCard, { borderTopColor: color }, theme.shadows.sm]}>
      <View style={styles.statTop}>
        <View style={[styles.statIconWrap, { backgroundColor: withAlpha(color, 0.12) }]}>
          {icon ? <AppIcon name={icon} size={18} color={color} /> : null}
        </View>
        <Text style={styles.statLabel}>{label}</Text>
      </View>
      <Text style={[styles.statValue, { color }]}>{value}</Text>
      {hint ? <Text style={styles.statHint}>{hint}</Text> : null}
    </View>
  );
}

export function Badge({
  label,
  color,
}: {
  label: string;
  color?: string;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const badgeColor = color ?? theme.colors.brand;

  return (
    <View style={[styles.badge, { backgroundColor: withAlpha(badgeColor, 0.12) }]}>
      <Text style={[styles.badgeText, { color: badgeColor }]}>{label}</Text>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    card: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      padding: theme.spacing.lg,
      borderWidth: 1,
      borderColor: theme.colors.border,
    },
    button: {
      minHeight: 48,
      borderRadius: theme.radius.md,
      alignItems: 'center',
      justifyContent: 'center',
      paddingHorizontal: theme.spacing.lg,
    },
    buttonCompact: {
      minHeight: 40,
      paddingHorizontal: theme.spacing.md,
    },
    buttonPrimary: {
      backgroundColor: theme.colors.brand,
    },
    buttonSecondary: {
      backgroundColor: theme.colors.surfaceMuted,
      borderWidth: 1,
      borderColor: theme.colors.border,
    },
    buttonGhost: {
      backgroundColor: 'transparent',
      borderWidth: 1,
      borderColor: theme.colors.border,
    },
    buttonInverse: {
      backgroundColor: 'rgba(255,255,255,0.12)',
      borderWidth: 1,
      borderColor: 'rgba(255,255,255,0.35)',
    },
    buttonDanger: {
      backgroundColor: theme.colors.danger,
    },
    buttonPressed: {
      opacity: 0.9,
      transform: [{ scale: 0.985 }],
    },
    buttonDisabled: {
      opacity: 0.5,
    },
    buttonContent: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    buttonText: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
    },
    buttonTextCompact: {
      ...theme.typography.label,
    },
    buttonTextInverse: {
      color: theme.colors.textInverse,
    },
    buttonTextGhost: {
      color: theme.colors.brand,
    },
    buttonTextSecondary: {
      color: theme.colors.text,
    },
    inputGroup: {
      gap: theme.spacing.xs,
    },
    inputLabel: {
      ...theme.typography.label,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    inputWrap: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      backgroundColor: theme.colors.surface,
      borderWidth: 1,
      borderColor: theme.colors.border,
      borderRadius: theme.radius.md,
      paddingHorizontal: theme.spacing.md,
    },
    inputWrapError: {
      borderColor: theme.colors.danger,
    },
    inputIcon: {
      marginLeft: theme.spacing.sm,
    },
    inputTrailing: {
      marginRight: theme.spacing.xs,
      padding: theme.spacing.xs,
    },
    input: {
      flex: 1,
      paddingVertical: theme.spacing.md,
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
    },
    inputErrorText: {
      ...theme.typography.caption,
      color: theme.colors.danger,
      textAlign: 'right',
    },
    statCard: {
      flex: 1,
      minWidth: '46%',
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      padding: theme.spacing.lg,
      borderWidth: 1,
      borderColor: theme.colors.border,
      borderTopWidth: 4,
      gap: theme.spacing.sm,
    },
    statTop: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: theme.spacing.sm,
    },
    statIconWrap: {
      width: 36,
      height: 36,
      borderRadius: theme.radius.sm,
      alignItems: 'center',
      justifyContent: 'center',
    },
    statLabel: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
      flex: 1,
    },
    statValue: {
      ...theme.typography.stat,
      textAlign: 'right',
    },
    statHint: {
      ...theme.typography.caption,
      color: theme.colors.textSubtle,
      textAlign: 'right',
    },
    badge: {
      alignSelf: 'flex-start',
      borderRadius: theme.radius.pill,
      paddingHorizontal: theme.spacing.md,
      paddingVertical: 5,
    },
    badgeText: {
      ...theme.typography.label,
    },
  });
}
