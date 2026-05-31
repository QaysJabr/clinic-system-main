import { useMemo } from 'react';
import { LinearGradient } from 'expo-linear-gradient';
import { StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { useAppTheme } from '@/providers/ThemeProvider';

export function BrandMark({
  size = 'md',
  showSubtitle = false,
  variant = 'default',
}: {
  size?: 'sm' | 'md' | 'lg';
  showSubtitle?: boolean;
  variant?: 'default' | 'inverse';
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme, variant), [theme, variant]);
  const dimensions = size === 'lg' ? 72 : size === 'md' ? 56 : 44;
  const iconSize = size === 'lg' ? 34 : size === 'md' ? 28 : 22;
  const inverse = variant === 'inverse';

  return (
    <View style={styles.wrap}>
      <LinearGradient
        colors={
          inverse
            ? ['rgba(255,255,255,0.28)', 'rgba(255,255,255,0.12)']
            : [theme.colors.brand, theme.colors.brandSecondary]
        }
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={[
          styles.logo,
          inverse && styles.logoInverse,
          { width: dimensions, height: dimensions, borderRadius: dimensions * 0.28 },
        ]}
      >
        <AppIcon name="stethoscope" size={iconSize} color={theme.colors.textInverse} strokeWidth={2} />
      </LinearGradient>
      <View style={styles.textWrap}>
        <Text style={[styles.title, size === 'lg' && styles.titleLg, inverse && styles.titleInverse]}>
          Clinic System
        </Text>
        {showSubtitle ? (
          <Text style={[styles.subtitle, inverse && styles.subtitleInverse]}>نظام إدارة العيادة</Text>
        ) : null}
      </View>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme'], variant: 'default' | 'inverse') {
  const inverse = variant === 'inverse';
  return StyleSheet.create({
    wrap: {
      alignItems: 'center',
      gap: theme.spacing.md,
    },
    logo: {
      alignItems: 'center',
      justifyContent: 'center',
      ...theme.shadows.lg,
    },
    logoInverse: {
      borderWidth: 1,
      borderColor: 'rgba(255,255,255,0.35)',
    },
    textWrap: {
      alignItems: 'center',
      gap: 4,
    },
    title: {
      ...theme.typography.title,
      color: inverse ? theme.colors.textInverse : theme.colors.brand,
    },
    titleLg: {
      ...theme.typography.display,
    },
    titleInverse: {
      color: theme.colors.textInverse,
    },
    subtitle: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
    },
    subtitleInverse: {
      color: 'rgba(255,255,255,0.85)',
    },
  });
}
