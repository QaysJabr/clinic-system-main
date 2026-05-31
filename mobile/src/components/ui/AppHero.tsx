import { useMemo } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import type { ComponentProps } from 'react';
import { AppIcon } from '@/components/ui/AppIcon';
import { ThemeToggle } from '@/components/ui/ThemeToggle';
import { Button } from '@/components/ui/primitives';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { useAppTheme } from '@/providers/ThemeProvider';

export function AppHero({
  title,
  caption,
  subtitle,
  subtitleIcon = 'building-2',
  onSettingsPress,
  settingsLabel = 'حسابي',
  showThemeToggle = true,
  edgeToTop = true,
}: {
  title: string;
  caption?: string;
  subtitle?: string;
  subtitleIcon?: ComponentProps<typeof AppIcon>['name'];
  onSettingsPress: () => void;
  settingsLabel?: string;
  showThemeToggle?: boolean;
  edgeToTop?: boolean;
}) {
  const { theme } = useAppTheme();
  const { top } = useScreenInsets();
  const styles = useMemo(() => createStyles(theme, edgeToTop ? top : 0), [theme, edgeToTop, top]);

  return (
    <LinearGradient
      colors={[theme.colors.brand, theme.colors.brandSecondary]}
      start={{ x: 0, y: 0 }}
      end={{ x: 1, y: 1 }}
      style={styles.hero}
    >
      <View style={styles.heroTop}>
        <View style={styles.heroText}>
          <Text style={styles.title}>{title}</Text>
          {caption ? <Text style={styles.caption}>{caption}</Text> : null}
          {subtitle ? (
            <View style={styles.subtitleRow}>
              <AppIcon name={subtitleIcon} size={16} color={theme.colors.brandLight} />
              <Text style={styles.subtitle}>{subtitle}</Text>
            </View>
          ) : null}
        </View>
        <View style={styles.heroActions}>
          {showThemeToggle ? <ThemeToggle compact /> : null}
          <Button label={settingsLabel} variant="inverse" icon="settings" compact onPress={onSettingsPress} />
        </View>
      </View>
    </LinearGradient>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme'], topInset: number) {
  return StyleSheet.create({
    hero: {
      paddingHorizontal: theme.spacing.lg,
      paddingTop: topInset + theme.spacing.lg,
      paddingBottom: theme.spacing.xl,
      borderBottomLeftRadius: theme.radius.xl,
      borderBottomRightRadius: theme.radius.xl,
    },
    heroTop: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      gap: theme.spacing.md,
    },
    heroActions: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    heroText: {
      flex: 1,
      alignItems: 'flex-end',
      gap: theme.spacing.xs,
    },
    title: {
      ...theme.typography.title,
      color: theme.colors.textInverse,
    },
    caption: {
      ...theme.typography.caption,
      color: theme.colors.brandLight,
      fontWeight: '600',
    },
    subtitleRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
      marginTop: theme.spacing.xs,
    },
    subtitle: {
      ...theme.typography.body,
      color: theme.colors.brandLight,
    },
  });
}
