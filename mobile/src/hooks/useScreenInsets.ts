import { useMemo } from 'react';
import { useSafeAreaInsets } from 'react-native-safe-area-context';

export function useScreenInsets() {
  const insets = useSafeAreaInsets();

  return useMemo(
    () => ({
      top: insets.top,
      bottom: insets.bottom,
      left: insets.left,
      right: insets.right,
      topPadding: insets.top + 12,
      bottomPadding: insets.bottom + 16,
      tabBarHeight: 56 + Math.max(insets.bottom, 8),
    }),
    [insets.top, insets.bottom, insets.left, insets.right],
  );
}
