import { RefreshControl, type RefreshControlProps } from 'react-native';
import { useAppTheme } from '@/providers/ThemeProvider';

export function RefreshControlThemed(props: Omit<RefreshControlProps, 'tintColor' | 'colors'>) {
  const { theme } = useAppTheme();
  return (
    <RefreshControl
      {...props}
      tintColor={theme.colors.brand}
      colors={[theme.colors.brand]}
    />
  );
}
