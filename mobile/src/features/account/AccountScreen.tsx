import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import Constants from 'expo-constants';
import { useAuth } from '@/auth/AuthContext';
import {
  exportPlatformClinicsCsv,
  exportPlatformPaymentsCsv,
} from '@/api/services/platform.service';
import { DateRangeFilter } from '@/features/platform/components/DateRangeFilter';
import { ScreenContainer } from '@/components/layout/Screen';
import { ScreenHeader } from '@/components/layout/ScreenHeader';
import {
  SettingsGroup,
  SettingsProfileHero,
  SettingsRow,
  SettingsSwitchRow,
} from '@/components/ui/settings/SettingsList';
import { useScreenInsets } from '@/hooks/useScreenInsets';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';
import { usePushSettings } from '@/hooks/usePushSettings';
import { isNativePushSupported } from '@/services/push/expo-go';
import { clinicPersonaLabel } from '@/auth/permissions';
import { isPlatformOwner } from '@/utils/persona';

export function AccountScreen({ mode }: { mode: 'clinic' | 'platform' }) {
  const { user, logout, biometricAvailable, biometricEnabled, biometricLabel, setBiometricEnabled } =
    useAuth();
  const { theme, isDark, toggleTheme } = useAppTheme();
  const { bottomPadding } = useScreenInsets();
  const styles = useMemo(() => createStyles(theme, bottomPadding), [theme, bottomPadding]);
  const toast = useToast();
  const [exporting, setExporting] = useState<'clinics' | 'payments' | null>(null);
  const [paidFrom, setPaidFrom] = useState('');
  const [paidTo, setPaidTo] = useState('');
  const [biometricBusy, setBiometricBusy] = useState(false);
  const pushSettings = usePushSettings();

  const appVersion = Constants.expoConfig?.version ?? '1.0.0';
  const personaLabel =
    mode === 'platform' || isPlatformOwner(user) ? 'مدير المنصّة' : clinicPersonaLabel(user);

  async function handleExport(type: 'clinics' | 'payments') {
    setExporting(type);
    try {
      if (type === 'clinics') {
        await exportPlatformClinicsCsv();
      } else {
        await exportPlatformPaymentsCsv(undefined, {
          paid_from: paidFrom.trim() || undefined,
          paid_to: paidTo.trim() || undefined,
        });
      }
      toast.showSuccess('تم تجهيز ملف التصدير');
    } catch (error) {
      toast.showError(formatApiError(error, 'تعذر تصدير البيانات'));
    } finally {
      setExporting(null);
    }
  }

  async function handleBiometricToggle(next: boolean) {
    setBiometricBusy(true);
    try {
      await setBiometricEnabled(next);
      toast.showSuccess(next ? `تم تفعيل ${biometricLabel}` : 'تم إيقاف البصمة');
    } catch (error) {
      toast.showError(formatApiError(error));
    } finally {
      setBiometricBusy(false);
    }
  }

  async function handlePushToggle(next: boolean) {
    const ok = await pushSettings.toggle(next);
    if (ok) {
      toast.showSuccess(next ? 'تم تفعيل الإشعارات الفورية' : 'تم إيقاف الإشعارات');
    } else {
      toast.showError('تعذر تحديث إعدادات الإشعارات');
    }
  }

  return (
    <ScreenContainer>
      <ScreenHeader title="الإعدادات" subtitle={personaLabel} />
      <ScrollView contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        <SettingsProfileHero
          name={user?.name}
          email={user?.email}
          subtitle={user?.clinic?.name ?? personaLabel}
        />

        <SettingsGroup title="المظهر">
          <SettingsSwitchRow
            icon="moon"
            iconColor={theme.colors.info}
            label="الوضع الداكن"
            value={isDark}
            onValueChange={() => toggleTheme()}
            last
          />
        </SettingsGroup>

        {biometricAvailable ? (
          <SettingsGroup
            title="الأمان"
            footer={`استخدم ${biometricLabel} للدخول السريع دون إدخال كلمة المرور في كل مرة.`}
          >
            <SettingsSwitchRow
              icon="fingerprint"
              iconColor={theme.colors.success}
              label={biometricLabel}
              value={biometricEnabled}
              onValueChange={handleBiometricToggle}
              disabled={biometricBusy}
              last
            />
          </SettingsGroup>
        ) : null}

        <SettingsGroup
          title="الإشعارات"
          footer={pushSettings.snapshot?.hint ?? 'تتطلب build التطبيق (APK) — لا تعمل في Expo Go.'}
        >
          {pushSettings.canToggle ? (
            <SettingsSwitchRow
              icon="bell"
              iconColor={theme.colors.brand}
              label="إشعارات فورية"
              value={Boolean(pushSettings.snapshot?.userEnabled)}
              onValueChange={handlePushToggle}
              disabled={pushSettings.busy}
              last={false}
            />
          ) : (
            <SettingsRow
              icon="bell"
              iconColor={theme.colors.textMuted}
              label="إشعارات فورية"
              value={pushSettings.snapshot?.label ?? (isNativePushSupported() ? '…' : 'Expo Go')}
              last={false}
            />
          )}
          <SettingsRow
            icon="wifi-off"
            iconColor={theme.colors.info}
            label="حالة الخادم"
            value={pushSettings.snapshot?.serverEnabled ? 'Push مفعّل' : 'غير مهيّأ'}
            last
          />
        </SettingsGroup>

        {mode === 'platform' ? (
          <>
            <DateRangeFilter
              title="فلتر تصدير المدفوعات"
              subtitle="اختياري — يُطبَّق على تصدير المدفوعات فقط"
              paidFrom={paidFrom}
              paidTo={paidTo}
              onPaidFromChange={setPaidFrom}
              onPaidToChange={setPaidTo}
            />
            <SettingsGroup title="تصدير البيانات" footer="CSV — مشاركة أو حفظ على الجهاز">
              <SettingsRow
                icon="download"
                label="تصدير العيادات"
                onPress={() => void handleExport('clinics')}
                last={false}
              />
              <SettingsRow
                icon="download"
                iconColor={theme.colors.info}
                label="تصدير المدفوعات"
                onPress={() => void handleExport('payments')}
                last
              />
            </SettingsGroup>
            {(exporting === 'clinics' || exporting === 'payments') && (
              <Text style={styles.exportingHint}>جاري تجهيز الملف…</Text>
            )}
          </>
        ) : null}

        <SettingsGroup title="حول التطبيق">
          <SettingsRow icon="info" label="الإصدار" value={`v${appVersion}`} last={false} />
          <SettingsRow
            icon="building-2"
            label="نوع الحساب"
            value={mode === 'platform' ? 'منصّة' : 'عيادة'}
            last
          />
        </SettingsGroup>

        <SettingsGroup>
          <SettingsRow
            icon="log-out"
            label="تسجيل الخروج"
            onPress={() => void logout()}
            destructive
            showChevron={false}
            last
          />
        </SettingsGroup>

        <Text style={styles.legal}>Clinic System · جميع الحقوق محفوظة</Text>
      </ScrollView>
    </ScreenContainer>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme'], bottomPadding: number) {
  return StyleSheet.create({
    content: {
      padding: theme.spacing.lg,
      gap: theme.spacing.lg,
      paddingBottom: bottomPadding + theme.spacing.lg,
    },
    exportingHint: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'center',
    },
    legal: {
      ...theme.typography.caption,
      color: theme.colors.textSubtle,
      textAlign: 'center',
      marginTop: theme.spacing.sm,
    },
  });
}
