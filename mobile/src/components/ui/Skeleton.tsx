import { useEffect, useMemo, useRef } from 'react';
import { Animated, StyleSheet, View, type ViewStyle } from 'react-native';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { useAppTheme } from '@/providers/ThemeProvider';

export function Skeleton({
  width = '100%',
  height = 16,
  style,
}: {
  width?: number | `${number}%`;
  height?: number;
  style?: ViewStyle;
}) {
  const { theme } = useAppTheme();
  const opacity = useRef(new Animated.Value(0.45)).current;

  useEffect(() => {
    const animation = Animated.loop(
      Animated.sequence([
        Animated.timing(opacity, { toValue: 0.9, duration: 700, useNativeDriver: true }),
        Animated.timing(opacity, { toValue: 0.45, duration: 700, useNativeDriver: true }),
      ]),
    );
    animation.start();
    return () => animation.stop();
  }, [opacity]);

  return (
    <Animated.View
      style={[
        {
          backgroundColor: theme.colors.skeleton,
          borderRadius: theme.radius.sm,
          width,
          height,
          opacity,
        },
        style,
      ]}
    />
  );
}

export function CardSkeleton() {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <View style={styles.card}>
      <View style={styles.cardHeader}>
        <Skeleton width={72} height={22} />
        <Skeleton width="35%" height={18} />
      </View>
      <Skeleton width="55%" height={18} style={styles.gapSm} />
      <Skeleton width="40%" height={14} style={styles.gapSm} />
    </View>
  );
}

export function ListScreenSkeleton({ count = 4 }: { count?: number }) {
  const { theme } = useAppTheme();
  const { top } = useScreenInsets();
  const styles = useMemo(() => createStyles(theme, top), [theme, top]);

  return (
    <View style={styles.listWrap}>
      <View style={styles.header}>
        <Skeleton width="45%" height={28} />
        <Skeleton width="30%" height={14} style={styles.gapSm} />
      </View>
      {Array.from({ length: count }).map((_, index) => (
        <CardSkeleton key={index} />
      ))}
    </View>
  );
}

export function DashboardSkeleton() {
  const { theme } = useAppTheme();
  const { top } = useScreenInsets();
  const styles = useMemo(() => createStyles(theme, top), [theme, top]);

  return (
    <View style={styles.dashboard}>
      <Skeleton width="100%" height={120} style={styles.hero} />
      <View style={styles.statsRow}>
        <Skeleton width="47%" height={110} />
        <Skeleton width="47%" height={110} />
        <Skeleton width="47%" height={110} />
        <Skeleton width="47%" height={110} />
      </View>
      <Skeleton width="100%" height={220} style={styles.panel} />
      <Skeleton width="100%" height={180} style={styles.panel} />
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme'], topInset = 0) {
  return StyleSheet.create({
    card: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      borderWidth: 1,
      borderColor: theme.colors.border,
      padding: theme.spacing.lg,
      ...theme.shadows.sm,
    },
    cardHeader: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'center',
      marginBottom: theme.spacing.sm,
    },
    gapSm: {
      marginTop: theme.spacing.sm,
    },
    listWrap: {
      padding: theme.spacing.lg,
      paddingTop: topInset + theme.spacing.md,
      gap: theme.spacing.md,
    },
    header: {
      alignItems: 'flex-end',
      marginBottom: theme.spacing.sm,
    },
    dashboard: {
      padding: theme.spacing.lg,
      paddingTop: topInset + theme.spacing.md,
      gap: theme.spacing.lg,
    },
    hero: {
      borderRadius: theme.radius.xl,
    },
    statsRow: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.md,
      justifyContent: 'space-between',
    },
    panel: {
      borderRadius: theme.radius.xl,
    },
  });
}
