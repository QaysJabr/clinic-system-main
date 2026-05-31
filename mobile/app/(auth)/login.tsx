import { useEffect, useMemo, useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { StatusBar } from 'expo-status-bar';
import Constants from 'expo-constants';
import { Button, Input } from '@/components/ui/primitives';
import { AppIcon } from '@/components/ui/AppIcon';
import { BrandMark } from '@/components/ui/BrandMark';
import { ThemeToggle } from '@/components/ui/ThemeToggle';
import { useAuth } from '@/auth/AuthContext';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { useAppTheme } from '@/providers/ThemeProvider';
import { formatApiError } from '@/utils/format';

export default function LoginScreen() {
  const {
    login,
    unlockWithBiometric,
    canUseBiometricUnlock,
    biometricAvailable,
    biometricLabel,
    setBiometricEnabled,
  } = useAuth();
  const { theme } = useAppTheme();
  const { top, bottom } = useScreenInsets();
  const styles = useMemo(() => createStyles(theme, top, bottom), [theme, top, bottom]);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [enableBiometric, setEnableBiometric] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [biometricLoading, setBiometricLoading] = useState(false);

  const appVersion = Constants.expoConfig?.version ?? '1.0.0';

  useEffect(() => {
    setError(null);
  }, [email, password]);

  async function handleLogin() {
    setError(null);
    setLoading(true);
    try {
      await login({ email: email.trim(), password });
      if (enableBiometric && biometricAvailable) {
        await setBiometricEnabled(true);
      }
    } catch (err) {
      setError(formatApiError(err, 'تعذر تسجيل الدخول'));
    } finally {
      setLoading(false);
    }
  }

  async function handleBiometricUnlock() {
    setError(null);
    setBiometricLoading(true);
    try {
      await unlockWithBiometric();
    } catch (err) {
      setError(formatApiError(err, 'تعذر الدخول بالبصمة'));
    } finally {
      setBiometricLoading(false);
    }
  }

  const canSubmit = email.trim().length > 0 && password.length > 0;

  return (
    <View style={styles.root}>
      <StatusBar style="light" />
      <LinearGradient
        colors={[theme.colors.brand, theme.colors.brandSecondary, theme.colors.brandDark]}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={StyleSheet.absoluteFill}
      />

      <KeyboardAvoidingView
        style={styles.flex}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 0 : 0}
      >
        <ScrollView
          contentContainerStyle={styles.scroll}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.topBar}>
            <ThemeToggle compact />
          </View>

          <View style={styles.hero}>
            <BrandMark size="lg" showSubtitle variant="inverse" />
            <Text style={styles.heroTagline}>إدارة العيادة من جيبك — بسرعة وأمان</Text>
          </View>

          {canUseBiometricUnlock ? (
            <View style={styles.biometricBlock}>
              <Pressable
                style={({ pressed }) => [styles.biometricOrb, pressed && styles.pressed]}
                onPress={handleBiometricUnlock}
                disabled={biometricLoading}
              >
                <View style={styles.biometricOrbInner}>
                  {biometricLoading ? (
                    <Text style={styles.biometricLoading}>…</Text>
                  ) : (
                    <AppIcon name="fingerprint" size={28} color={theme.colors.textInverse} />
                  )}
                </View>
              </Pressable>
              <Text style={styles.biometricHint}>دخول سريع بـ {biometricLabel}</Text>
              <View style={styles.dividerRow}>
                <View style={styles.dividerLine} />
                <Text style={styles.dividerText}>أو بالبريد</Text>
                <View style={styles.dividerLine} />
              </View>
            </View>
          ) : null}

          <View style={styles.card}>
            <Text style={styles.cardTitle}>تسجيل الدخول</Text>
            <Text style={styles.cardSubtitle}>حساب العيادة أو المنصّة</Text>

            <View style={styles.form}>
              <Input
                label="البريد الإلكتروني"
                value={email}
                onChangeText={setEmail}
                icon="mail"
                autoCapitalize="none"
                keyboardType="email-address"
                textContentType="username"
                autoComplete="email"
                placeholder="admin@clinic.local"
                returnKeyType="next"
              />
              <Input
                label="كلمة المرور"
                value={password}
                onChangeText={setPassword}
                icon="lock"
                secureTextEntry
                secureToggle
                textContentType="password"
                autoComplete="password"
                placeholder="••••••••"
                returnKeyType="done"
                onSubmitEditing={() => {
                  if (canSubmit) {
                    void handleLogin();
                  }
                }}
              />

              {biometricAvailable && !canUseBiometricUnlock ? (
                <Pressable
                  style={styles.biometricToggle}
                  onPress={() => setEnableBiometric((value) => !value)}
                >
                  <View style={[styles.checkbox, enableBiometric && styles.checkboxActive]}>
                    {enableBiometric ? <Text style={styles.checkmark}>✓</Text> : null}
                  </View>
                  <Text style={styles.biometricToggleText}>
                    تفعيل {biometricLabel} بعد أول دخول
                  </Text>
                </Pressable>
              ) : null}

              {error ? (
                <View style={styles.errorBox}>
                  <Text style={styles.error}>{error}</Text>
                </View>
              ) : null}

              <Button
                label="تسجيل الدخول"
                onPress={handleLogin}
                loading={loading}
                disabled={!canSubmit}
              />
            </View>
          </View>

          <View style={styles.footer}>
            <View style={styles.footerPills}>
              <View style={styles.pill}>
                <Text style={styles.pillText}>🏥 عيادة</Text>
              </View>
              <View style={styles.pill}>
                <Text style={styles.pillText}>☁️ منصّة SaaS</Text>
              </View>
            </View>
            <Text style={styles.footerHint}>
              نفس الحساب يوجّهك تلقائياً للوحة المناسبة
            </Text>
            <Text style={styles.version}>Clinic System · v{appVersion}</Text>
          </View>
        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  );
}

function createStyles(
  theme: ReturnType<typeof useAppTheme>['theme'],
  topInset: number,
  bottomInset: number,
) {
  return StyleSheet.create({
    root: {
      flex: 1,
      backgroundColor: theme.colors.brand,
    },
    flex: {
      flex: 1,
    },
    scroll: {
      flexGrow: 1,
      paddingTop: topInset + theme.spacing.sm,
      paddingBottom: bottomInset + theme.spacing.xl,
      paddingHorizontal: theme.spacing.lg,
      gap: theme.spacing.lg,
    },
    topBar: {
      flexDirection: 'row-reverse',
      justifyContent: 'flex-start',
    },
    hero: {
      alignItems: 'center',
      gap: theme.spacing.md,
      paddingVertical: theme.spacing.md,
    },
    heroTagline: {
      ...theme.typography.body,
      color: 'rgba(255,255,255,0.88)',
      textAlign: 'center',
      lineHeight: 22,
      paddingHorizontal: theme.spacing.lg,
    },
    biometricBlock: {
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    biometricOrb: {
      width: 72,
      height: 72,
      borderRadius: 36,
      backgroundColor: 'rgba(255,255,255,0.18)',
      borderWidth: 1,
      borderColor: 'rgba(255,255,255,0.35)',
      alignItems: 'center',
      justifyContent: 'center',
    },
    biometricOrbInner: {
      width: 56,
      height: 56,
      borderRadius: 28,
      backgroundColor: 'rgba(255,255,255,0.22)',
      alignItems: 'center',
      justifyContent: 'center',
    },
    biometricEmoji: {
      fontSize: 26,
    },
    biometricLoading: {
      color: theme.colors.textInverse,
      fontSize: 24,
    },
    biometricHint: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.9)',
    },
    dividerRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
      width: '100%',
      marginTop: theme.spacing.sm,
    },
    dividerLine: {
      flex: 1,
      height: StyleSheet.hairlineWidth,
      backgroundColor: 'rgba(255,255,255,0.35)',
    },
    dividerText: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.75)',
    },
    card: {
      backgroundColor: theme.colors.surface,
      borderRadius: theme.radius.xl,
      padding: theme.spacing.xl,
      gap: theme.spacing.lg,
      ...theme.shadows.lg,
    },
    cardTitle: {
      ...theme.typography.title,
      color: theme.colors.text,
      textAlign: 'center',
    },
    cardSubtitle: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
      textAlign: 'center',
    },
    form: {
      gap: theme.spacing.lg,
    },
    biometricToggle: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      gap: theme.spacing.sm,
    },
    checkbox: {
      width: 22,
      height: 22,
      borderRadius: theme.radius.sm,
      borderWidth: 2,
      borderColor: theme.colors.borderStrong,
      alignItems: 'center',
      justifyContent: 'center',
    },
    checkboxActive: {
      backgroundColor: theme.colors.brand,
      borderColor: theme.colors.brand,
    },
    checkmark: {
      color: theme.colors.textInverse,
      fontSize: 14,
      fontFamily: theme.fonts.bold,
    },
    biometricToggleText: {
      ...theme.typography.body,
      color: theme.colors.text,
      flex: 1,
      textAlign: 'right',
    },
    errorBox: {
      backgroundColor: theme.colors.dangerSoft,
      borderRadius: theme.radius.md,
      padding: theme.spacing.md,
      borderWidth: StyleSheet.hairlineWidth,
      borderColor: theme.colors.danger,
    },
    error: {
      ...theme.typography.body,
      color: theme.colors.danger,
      textAlign: 'center',
    },
    footer: {
      alignItems: 'center',
      gap: theme.spacing.sm,
      paddingTop: theme.spacing.sm,
    },
    footerPills: {
      flexDirection: 'row-reverse',
      gap: theme.spacing.sm,
    },
    pill: {
      backgroundColor: 'rgba(255,255,255,0.14)',
      borderRadius: theme.radius.pill,
      paddingHorizontal: theme.spacing.md,
      paddingVertical: theme.spacing.xs,
      borderWidth: 1,
      borderColor: 'rgba(255,255,255,0.22)',
    },
    pillText: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.92)',
      fontWeight: '600',
    },
    footerHint: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.75)',
      textAlign: 'center',
      lineHeight: 18,
    },
    version: {
      ...theme.typography.caption,
      color: 'rgba(255,255,255,0.55)',
      marginTop: theme.spacing.xs,
    },
    pressed: {
      opacity: 0.88,
    },
  });
}
