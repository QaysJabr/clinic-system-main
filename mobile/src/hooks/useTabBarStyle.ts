import { useMemo } from 'react';
import { Platform, StyleSheet, type ViewStyle } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { useAppTheme } from '@/providers/ThemeProvider';

export function useTabBarStyle(): ViewStyle {
  const { theme } = useAppTheme();
  const insets = useSafeAreaInsets();
  const bottomInset = Math.max(insets.bottom, Platform.OS === 'ios' ? 8 : 6);

  return useMemo(
    () => ({
      backgroundColor: theme.colors.tabBar,
      borderTopColor: theme.colors.border,
      borderTopWidth: StyleSheet.hairlineWidth,
      height: 56 + bottomInset + 8,
      paddingTop: 6,
      paddingBottom: bottomInset,
      elevation: 0,
      ...theme.shadows.sm,
    }),
    [theme, bottomInset],
  );
}
