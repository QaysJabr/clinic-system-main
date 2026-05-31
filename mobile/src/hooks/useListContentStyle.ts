import { useMemo } from 'react';
import { useAppTheme } from '@/providers/ThemeProvider';

export function useListContentStyle() {
  const { theme } = useAppTheme();
  return useMemo(
    () => ({
      padding: theme.spacing.lg,
      gap: theme.spacing.md,
      paddingBottom: theme.spacing.xxl,
    }),
    [theme.spacing.lg, theme.spacing.md, theme.spacing.xxl],
  );
}
