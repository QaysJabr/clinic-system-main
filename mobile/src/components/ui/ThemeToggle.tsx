import { Pressable, StyleSheet } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { useAppTheme } from '@/providers/ThemeProvider';
import { fireHapticLight } from '@/utils/haptics';

export function ThemeToggle({ compact = false }: { compact?: boolean }) {
  const { theme, isDark, toggleTheme } = useAppTheme();
  const styles = createStyles(theme, compact);

  return (
    <Pressable
      style={({ pressed }) => [styles.button, pressed && styles.pressed]}
      onPress={() => {
        fireHapticLight();
        toggleTheme();
      }}
      hitSlop={8}
    >
      <AppIcon
        name={isDark ? 'sun' : 'moon'}
        size={compact ? 18 : 20}
        color={compact ? theme.colors.textInverse : theme.colors.brand}
      />
    </Pressable>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme'], compact: boolean) {
  return StyleSheet.create({
    button: {
      width: compact ? 36 : 40,
      height: compact ? 36 : 40,
      borderRadius: theme.radius.sm,
      alignItems: 'center',
      justifyContent: 'center',
      backgroundColor: compact ? 'rgba(255,255,255,0.14)' : theme.colors.surfaceMuted,
      borderWidth: compact ? 1 : 0,
      borderColor: compact ? 'rgba(255,255,255,0.25)' : theme.colors.border,
    },
    pressed: {
      opacity: 0.85,
    },
  });
}
