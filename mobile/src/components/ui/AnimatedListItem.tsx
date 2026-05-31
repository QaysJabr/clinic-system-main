import Animated, { FadeInDown } from 'react-native-reanimated';
import type { ReactNode } from 'react';

export function AnimatedListItem({
  index,
  children,
}: {
  index: number;
  children: ReactNode;
}) {
  return (
    <Animated.View
      entering={FadeInDown.delay(Math.min(index * 45, 360))
        .duration(320)
        .springify()
        .damping(18)}
    >
      {children}
    </Animated.View>
  );
}
