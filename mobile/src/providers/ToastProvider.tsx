import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState, type ReactNode } from 'react';
import { Animated, Pressable, StyleSheet, Text } from 'react-native';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import { AppIcon } from '@/components/ui/AppIcon';
import type { ComponentProps } from 'react';
import { useAppTheme } from '@/providers/ThemeProvider';

type ToastType = 'success' | 'error' | 'info';

interface ToastPayload {
  type: ToastType;
  message: string;
}

interface ToastContextValue {
  showSuccess: (message: string) => void;
  showError: (message: string) => void;
  showInfo: (message: string) => void;
}

const ToastContext = createContext<ToastContextValue | null>(null);

const toastMeta = (
  theme: ReturnType<typeof useAppTheme>['theme'],
): Record<
  ToastType,
  { bg: string; border: string; icon: ComponentProps<typeof AppIcon>['name'] }
> => ({
  success: { bg: theme.colors.successSoft, border: theme.colors.success, icon: 'check' },
  error: { bg: theme.colors.dangerSoft, border: theme.colors.danger, icon: 'alert-circle' },
  info: { bg: theme.colors.infoSoft, border: theme.colors.info, icon: 'bell' },
});

function ToastBanner({
  toast,
  onHide,
}: {
  toast: ToastPayload;
  onHide: () => void;
}) {
  const { theme } = useAppTheme();
  const insets = useSafeAreaInsets();
  const translateY = useRef(new Animated.Value(-24)).current;
  const opacity = useRef(new Animated.Value(0)).current;
  const palette = toastMeta(theme)[toast.type];
  const styles = useMemo(() => createToastStyles(theme), [theme]);

  useEffect(() => {
    Animated.parallel([
      Animated.timing(translateY, { toValue: 0, duration: 220, useNativeDriver: true }),
      Animated.timing(opacity, { toValue: 1, duration: 220, useNativeDriver: true }),
    ]).start();
  }, [opacity, translateY]);

  return (
    <Animated.View
      style={[
        styles.wrap,
        {
          top: insets.top + theme.spacing.sm,
          opacity,
          transform: [{ translateY }],
          backgroundColor: palette.bg,
          borderColor: palette.border,
        },
      ]}
    >
      <Pressable style={styles.content} onPress={onHide}>
        <AppIcon name={palette.icon} size={18} color={palette.border} />
        <Text style={[styles.message, { color: palette.border }]}>{toast.message}</Text>
      </Pressable>
    </Animated.View>
  );
}

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toast, setToast] = useState<ToastPayload | null>(null);
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  const hide = useCallback(() => {
    setToast(null);
    if (timerRef.current) {
      clearTimeout(timerRef.current);
      timerRef.current = null;
    }
  }, []);

  const show = useCallback(
    (type: ToastType, message: string) => {
      hide();
      setToast({ type, message });
      timerRef.current = setTimeout(hide, 3200);
    },
    [hide],
  );

  const value = useMemo<ToastContextValue>(
    () => ({
      showSuccess: (message) => show('success', message),
      showError: (message) => show('error', message),
      showInfo: (message) => show('info', message),
    }),
    [show],
  );

  return (
    <ToastContext.Provider value={value}>
      {children}
      {toast ? <ToastBanner toast={toast} onHide={hide} /> : null}
    </ToastContext.Provider>
  );
}

export function useToast(): ToastContextValue {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within ToastProvider');
  }
  return context;
}

const createToastStyles = (theme: ReturnType<typeof useAppTheme>['theme']) =>
  StyleSheet.create({
    wrap: {
      position: 'absolute',
      left: theme.spacing.lg,
      right: theme.spacing.lg,
      zIndex: 999,
      borderRadius: theme.radius.md,
      borderWidth: 1,
      ...theme.shadows.md,
    },
    content: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
    },
    message: {
      ...theme.typography.body,
      flex: 1,
      textAlign: 'right',
    },
  });
