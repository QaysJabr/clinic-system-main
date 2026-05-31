import { useMemo, type ReactNode } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  View,
  type ViewStyle,
} from 'react-native';
import { ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ScreenHeader } from '@/components/layout/ScreenHeader';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { useAppTheme } from '@/providers/ThemeProvider';

type HeaderProps = {
  title: string;
  subtitle?: string;
  showBack?: boolean;
  right?: ReactNode;
};

/** Scrollable detail/form screen with unified safe-area padding. */
export function FormScreen({
  title,
  subtitle,
  showBack = true,
  right,
  children,
  header,
  contentStyle,
  keyboardAvoiding = true,
}: HeaderProps & {
  children: ReactNode;
  header?: ReactNode;
  contentStyle?: ViewStyle;
  keyboardAvoiding?: boolean;
}) {
  const { theme } = useAppTheme();
  const { bottomPadding } = useScreenInsets();
  const styles = useMemo(
    () =>
      StyleSheet.create({
        flex: { flex: 1 },
        content: {
          padding: theme.spacing.lg,
          gap: theme.spacing.lg,
          paddingBottom: bottomPadding + theme.spacing.lg,
        },
      }),
    [theme, bottomPadding],
  );

  const body = (
    <>
      <ScreenHeader title={title} subtitle={subtitle} showBack={showBack} right={right} />
      {header}
      <ScrollView
        contentContainerStyle={[styles.content, contentStyle]}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        {children}
      </ScrollView>
    </>
  );

  return (
    <ScreenContainer>
      {keyboardAvoiding ? (
        <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
          {body}
        </KeyboardAvoidingView>
      ) : (
        body
      )}
    </ScreenContainer>
  );
}

/** For screens where the form component owns its own ScrollView. */
export function StackFormScreen({
  title,
  subtitle,
  showBack = true,
  right,
  children,
  header,
}: HeaderProps & {
  children: ReactNode;
  header?: ReactNode;
}) {
  const styles = useMemo(() => StyleSheet.create({ flex: { flex: 1 } }), []);

  return (
    <ScreenContainer>
      <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
        <ScreenHeader title={title} subtitle={subtitle} showBack={showBack} right={right} />
        {header}
        <View style={styles.flex}>{children}</View>
      </KeyboardAvoidingView>
    </ScreenContainer>
  );
}

export function FormScreenLoading({
  title,
  subtitle,
  count = 2,
}: HeaderProps & { count?: number }) {
  return (
    <ScreenContainer>
      <ScreenHeader title={title} subtitle={subtitle} />
      <ListScreenSkeleton count={count} />
    </ScreenContainer>
  );
}

export function FormScreenError({
  title,
  subtitle,
  message,
  onRetry,
}: HeaderProps & { message: string; onRetry?: () => void }) {
  return (
    <ScreenContainer>
      <ScreenHeader title={title} subtitle={subtitle} />
      <ErrorView message={message} onRetry={onRetry} />
    </ScreenContainer>
  );
}

/** Shared scroll content padding for embedded form ScrollViews. */
export function useFormScrollPadding() {
  const { theme } = useAppTheme();
  const { bottomPadding } = useScreenInsets();
  return useMemo(
    () => ({
      padding: theme.spacing.lg,
      gap: theme.spacing.lg,
      paddingBottom: bottomPadding + theme.spacing.lg,
    }),
    [theme.spacing.lg, bottomPadding],
  );
}
