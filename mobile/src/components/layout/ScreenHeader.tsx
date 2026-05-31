import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { AppIcon } from '@/components/ui/AppIcon';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { useAppTheme } from '@/providers/ThemeProvider';

interface ScreenHeaderProps {
  title: string;
  subtitle?: string;
  showBack?: boolean;
  right?: React.ReactNode;
}

export function ScreenHeader({ title, subtitle, showBack = true, right }: ScreenHeaderProps) {
  const router = useRouter();
  const { theme } = useAppTheme();
  const { top } = useScreenInsets();
  const styles = useMemo(() => createStyles(theme, top), [theme, top]);

  return (
    <View style={styles.wrap}>
      <View style={styles.row}>
        <View style={styles.side}>{right ?? null}</View>
        <View style={styles.center}>
          <Text style={styles.title}>{title}</Text>
          {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
        </View>
        {showBack ? (
          <Pressable onPress={() => router.back()} style={styles.backBtn} hitSlop={8}>
            <AppIcon name="chevron-right" size={24} color={theme.colors.brand} />
          </Pressable>
        ) : (
          <View style={styles.side} />
        )}
      </View>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme'], topInset: number) {
  return StyleSheet.create({
    wrap: {
      backgroundColor: theme.colors.surface,
      borderBottomWidth: StyleSheet.hairlineWidth,
      borderBottomColor: theme.colors.border,
      paddingTop: topInset,
      ...theme.shadows.sm,
    },
    row: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      gap: theme.spacing.sm,
    },
    side: {
      minWidth: 40,
      alignItems: 'center',
    },
    center: {
      flex: 1,
      alignItems: 'center',
    },
    title: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
    },
    subtitle: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      marginTop: 2,
    },
    backBtn: {
      width: 40,
      height: 40,
      borderRadius: theme.radius.sm,
      backgroundColor: theme.colors.surfaceMuted,
      alignItems: 'center',
      justifyContent: 'center',
    },
  });
}
