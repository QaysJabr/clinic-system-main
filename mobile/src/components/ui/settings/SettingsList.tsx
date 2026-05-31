import { useMemo } from 'react';
import { Pressable, StyleSheet, Switch, Text, View, type ViewStyle } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import type { ComponentProps } from 'react';
import { AppIcon } from '@/components/ui/AppIcon';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';
import { fireHapticLight } from '@/utils/haptics';

export function SettingsProfileHero({
  name,
  email,
  subtitle,
}: {
  name?: string;
  email?: string;
  subtitle?: string;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <LinearGradient
      colors={[theme.colors.brand, theme.colors.brandSecondary]}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
      style={styles.hero}
    >
      <View style={styles.avatarRing}>
        <View style={styles.avatar}>
          <AppIcon name="circle-user" size={36} color={theme.colors.brand} />
        </View>
      </View>
      <Text style={styles.heroName}>{name ?? '—'}</Text>
      {email ? <Text style={styles.heroEmail}>{email}</Text> : null}
      {subtitle ? <Text style={styles.heroSubtitle}>{subtitle}</Text> : null}
    </LinearGradient>
  );
}

export function SettingsGroup({
  title,
  footer,
  children,
  style,
}: {
  title?: string;
  footer?: string;
  children: React.ReactNode;
  style?: ViewStyle;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <View style={style}>
      {title ? <Text style={styles.groupTitle}>{title}</Text> : null}
      <View style={styles.group}>{children}</View>
      {footer ? <Text style={styles.groupFooter}>{footer}</Text> : null}
    </View>
  );
}

export function SettingsRow({
  icon,
  iconColor,
  label,
  value,
  onPress,
  showChevron = Boolean(onPress),
  destructive = false,
  last = false,
}: {
  icon?: ComponentProps<typeof AppIcon>['name'];
  iconColor?: string;
  label: string;
  value?: string;
  onPress?: () => void;
  showChevron?: boolean;
  destructive?: boolean;
  last?: boolean;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const accent = iconColor ?? (destructive ? theme.colors.danger : theme.colors.brand);

  const content = (
    <>
      {icon ? (
        <View style={[styles.iconWrap, { backgroundColor: withAlpha(accent, 0.12) }]}>
          <AppIcon name={icon} size={18} color={accent} />
        </View>
      ) : null}
      <Text style={[styles.rowLabel, destructive && styles.rowLabelDanger]}>{label}</Text>
      <View style={styles.rowTrailing}>
        {value ? <Text style={styles.rowValue}>{value}</Text> : null}
        {showChevron && onPress ? (
          <AppIcon name="chevron-left" size={18} color={theme.colors.textSubtle} />
        ) : null}
      </View>
    </>
  );

  if (onPress) {
    return (
      <Pressable
        onPress={() => {
          fireHapticLight();
          onPress();
        }}
        style={({ pressed }) => [
          styles.row,
          !last && styles.rowBorder,
          pressed && styles.rowPressed,
        ]}
      >
        {content}
      </Pressable>
    );
  }

  return <View style={[styles.row, !last && styles.rowBorder]}>{content}</View>;
}

export function SettingsSwitchRow({
  icon,
  iconColor,
  label,
  value,
  onValueChange,
  disabled,
  last = false,
}: {
  icon?: ComponentProps<typeof AppIcon>['name'];
  iconColor?: string;
  label: string;
  value: boolean;
  onValueChange: (next: boolean) => void;
  disabled?: boolean;
  last?: boolean;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const accent = iconColor ?? theme.colors.brand;

  return (
    <View style={[styles.row, !last && styles.rowBorder]}>
      {icon ? (
        <View style={[styles.iconWrap, { backgroundColor: withAlpha(accent, 0.12) }]}>
          <AppIcon name={icon} size={18} color={accent} />
        </View>
      ) : null}
      <Text style={styles.rowLabel}>{label}</Text>
      <Switch
        value={value}
        onValueChange={onValueChange}
        disabled={disabled}
        trackColor={{ false: theme.colors.borderStrong, true: withAlpha(theme.colors.brand, 0.45) }}
        thumbColor={value ? theme.colors.brand : theme.colors.surface}
        ios_backgroundColor={theme.colors.borderStrong}
      />
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    hero: {
      alignItems: 'center',
      paddingVertical: theme.spacing.xl,
      paddingHorizontal: theme.spacing.lg,
      borderRadius: theme.radius.xl,
      gap: theme.spacing.sm,
      ...theme.shadows.md,
    },
    avatarRing: {
      padding: 3,
      borderRadius: 999,
      backgroundColor: 'rgba(255,255,255,0.25)',
      marginBottom: theme.spacing.xs,
    },
    avatar: {
      width: 72,
      height: 72,
      borderRadius: 36,
      backgroundColor: theme.colors.textInverse,
      alignItems: 'center',
      justifyContent: 'center',
    },
    heroName: {
      ...theme.typography.title,
      color: theme.colors.textInverse,
    },
    heroEmail: {
      ...theme.typography.body,
      color: theme.colors.brandLight,
    },
    heroSubtitle: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.85)',
      marginTop: 2,
    },
    groupTitle: {
      ...theme.typography.label,
      color: theme.colors.textMuted,
      textAlign: 'right',
      marginBottom: theme.spacing.sm,
      marginHorizontal: theme.spacing.xs,
      textTransform: 'uppercase',
    },
    groupFooter: {
      ...theme.typography.caption,
      color: theme.colors.textSubtle,
      textAlign: 'right',
      marginTop: theme.spacing.sm,
      marginHorizontal: theme.spacing.xs,
      lineHeight: 18,
    },
    group: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      borderWidth: StyleSheet.hairlineWidth,
      borderColor: theme.colors.border,
      overflow: 'hidden',
      ...theme.shadows.sm,
    },
    row: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.md,
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      minHeight: 52,
    },
    rowBorder: {
      borderBottomWidth: StyleSheet.hairlineWidth,
      borderBottomColor: theme.colors.border,
    },
    rowPressed: {
      backgroundColor: theme.colors.surfaceMuted,
    },
    iconWrap: {
      width: 32,
      height: 32,
      borderRadius: theme.radius.sm,
      alignItems: 'center',
      justifyContent: 'center',
    },
    rowLabel: {
      ...theme.typography.body,
      color: theme.colors.text,
      flex: 1,
      textAlign: 'right',
    },
    rowLabelDanger: {
      color: theme.colors.danger,
    },
    rowTrailing: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    rowValue: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
    },
  });
}
