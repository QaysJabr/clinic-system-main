import { useMemo } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import { withAlpha } from '@/config/theme';
import { useSemanticColors } from '@/hooks/useSemanticColors';
import { paymentSourceLabel } from '@/features/platform/utils/platform-format';
import { useAppTheme } from '@/providers/ThemeProvider';

export function PaymentSourceBadge({ source }: { source: string }) {
  const { theme } = useAppTheme();
  const colors = useSemanticColors();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const color = colors.paymentSource(source);

  return (
    <View style={[styles.badge, { backgroundColor: withAlpha(color, 0.12) }]}>
      <Text style={[styles.text, { color }]}>{paymentSourceLabel(source)}</Text>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    badge: {
      alignSelf: 'flex-end',
      borderRadius: theme.radius.pill,
      paddingHorizontal: theme.spacing.sm,
      paddingVertical: 4,
    },
    text: {
      ...theme.typography.caption,
      fontWeight: '600',
    },
  });
}
