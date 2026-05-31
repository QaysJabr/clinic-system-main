import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import type { ComponentProps } from 'react';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';

export function QuickActionsBar({
  actions,
}: {
  actions: Array<{
    label: string;
    icon: ComponentProps<typeof AppIcon>['name'];
    onPress: () => void;
    accent?: string;
  }>;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  if (actions.length === 0) {
    return null;
  }

  return (
    <View style={styles.grid}>
      {actions.map((action) => {
        const color = action.accent ?? theme.colors.brand;
        return (
          <Pressable
            key={action.label}
            onPress={action.onPress}
            style={({ pressed }) => [styles.item, pressed && styles.pressed]}
          >
            <View style={[styles.iconWrap, { backgroundColor: withAlpha(color, 0.1) }]}>
              <AppIcon name={action.icon} size={20} color={color} />
            </View>
            <Text style={styles.label}>{action.label}</Text>
          </Pressable>
        );
      })}
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    grid: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.sm,
      paddingHorizontal: theme.spacing.lg,
    },
    item: {
      width: '48%',
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.lg,
      padding: theme.spacing.md,
      alignItems: 'flex-end',
      gap: theme.spacing.sm,
      borderWidth: 1,
      borderColor: theme.colors.border,
      ...theme.shadows.sm,
    },
    iconWrap: {
      width: 40,
      height: 40,
      borderRadius: 20,
      alignItems: 'center',
      justifyContent: 'center',
    },
    label: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
      fontWeight: '600',
    },
    pressed: { opacity: 0.92 },
  });
}
