import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react';
import Constants from 'expo-constants';
import { fetchCurrentUser, login as loginRequest, logout as logoutRequest } from '@/api/services/auth.service';
import { setUnauthorizedHandler } from '@/api/client';
import {
  authenticateWithBiometric,
  getBiometricLabel,
  isBiometricAvailable,
} from '@/auth/biometric-auth';
import { getBiometricEnabled, setBiometricEnabled as persistBiometricEnabled } from '@/auth/biometric-prefs';
import { clearToken, getToken, setToken } from '@/auth/token-storage';
import { queryClient } from '@/lib/query-client';
import { isNativePushSupported } from '@/services/push/expo-go';
import { getStoredPushToken } from '@/services/push/push-token-storage';
import type { LoginPayload, User } from '@/types/api';

interface AuthContextValue {
  user: User | null;
  isAuthenticated: boolean;
  isBootstrapping: boolean;
  biometricAvailable: boolean;
  biometricEnabled: boolean;
  biometricLabel: string;
  canUseBiometricUnlock: boolean;
  login: (payload: Omit<LoginPayload, 'device_name'>) => Promise<void>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
  unlockWithBiometric: () => Promise<void>;
  setBiometricEnabled: (enabled: boolean) => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | null>(null);

function deviceName(): string {
  return Constants.deviceName ?? 'Clinic Mobile';
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isBootstrapping, setIsBootstrapping] = useState(true);
  const [biometricAvailable, setBiometricAvailable] = useState(false);
  const [biometricEnabled, setBiometricEnabledState] = useState(false);
  const [biometricLabel, setBiometricLabel] = useState('الدخول السريع');
  const [canUseBiometricUnlock, setCanUseBiometricUnlock] = useState(false);

  const refreshUser = useCallback(async () => {
    const me = await fetchCurrentUser();
    setUser(me);
  }, []);

  const syncBiometricState = useCallback(async () => {
    const [available, enabled, label, token] = await Promise.all([
      isBiometricAvailable(),
      getBiometricEnabled(),
      getBiometricLabel(),
      getToken(),
    ]);
    setBiometricAvailable(available);
    setBiometricEnabledState(enabled);
    setBiometricLabel(label);
    setCanUseBiometricUnlock(Boolean(token) && enabled && available);
  }, []);

  const bootstrap = useCallback(async () => {
    setIsBootstrapping(true);
    try {
      const [token, enabled, available] = await Promise.all([
        getToken(),
        getBiometricEnabled(),
        isBiometricAvailable(),
      ]);

      setBiometricAvailable(available);
      setBiometricEnabledState(enabled);
      setBiometricLabel(await getBiometricLabel());

      if (!token) {
        setUser(null);
        setCanUseBiometricUnlock(false);
        return;
      }

      setCanUseBiometricUnlock(enabled && available);

      if (enabled && available) {
        const ok = await authenticateWithBiometric();
        if (!ok) {
          setUser(null);
          return;
        }
      }

      await refreshUser();
    } catch {
      await clearToken();
      setUser(null);
      setCanUseBiometricUnlock(false);
    } finally {
      setIsBootstrapping(false);
    }
  }, [refreshUser]);

  useEffect(() => {
    void bootstrap();
  }, [bootstrap]);

  useEffect(() => {
    setUnauthorizedHandler(() => {
      setUser(null);
      queryClient.clear();
      void syncBiometricState();
    });
    return () => setUnauthorizedHandler(null);
  }, [syncBiometricState]);

  const login = useCallback(async (payload: Omit<LoginPayload, 'device_name'>) => {
    const result = await loginRequest({
      ...payload,
      device_name: deviceName(),
    });
    await setToken(result.token);
    setUser(result.user);
    queryClient.clear();
    await syncBiometricState();
    if (isNativePushSupported()) {
      void import('@/services/push/push-manager').then(({ syncPushRegistration }) => syncPushRegistration());
    }
  }, [syncBiometricState]);

  const logout = useCallback(async () => {
    const fcmToken = await getStoredPushToken();
    try {
      await logoutRequest(fcmToken ?? undefined);
    } catch {
      // Token may already be invalid; still clear local session.
    } finally {
      if (isNativePushSupported()) {
        const { syncPushUnregistration } = await import('@/services/push/push-manager');
        await syncPushUnregistration();
      } else {
        const { clearStoredPushToken } = await import('@/services/push/push-token-storage');
        await clearStoredPushToken();
      }
      await clearToken();
      setUser(null);
      queryClient.clear();
      await syncBiometricState();
    }
  }, [syncBiometricState]);

  const unlockWithBiometric = useCallback(async () => {
    const token = await getToken();
    if (!token) {
      throw new Error('لا توجد جلسة محفوظة');
    }
    const ok = await authenticateWithBiometric();
    if (!ok) {
      throw new Error('تعذر التحقق بالبصمة');
    }
    await refreshUser();
  }, [refreshUser]);

  const setBiometricEnabled = useCallback(async (enabled: boolean) => {
    if (enabled) {
      const available = await isBiometricAvailable();
      if (!available) {
        throw new Error('البصمة غير متاحة على هذا الجهاز');
      }
      const ok = await authenticateWithBiometric();
      if (!ok) {
        throw new Error('تعذر تفعيل البصمة');
      }
    }
    await persistBiometricEnabled(enabled);
    await syncBiometricState();
  }, [syncBiometricState]);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      isAuthenticated: user !== null,
      isBootstrapping,
      biometricAvailable,
      biometricEnabled,
      biometricLabel,
      canUseBiometricUnlock,
      login,
      logout,
      refreshUser,
      unlockWithBiometric,
      setBiometricEnabled,
    }),
    [
      user,
      isBootstrapping,
      biometricAvailable,
      biometricEnabled,
      biometricLabel,
      canUseBiometricUnlock,
      login,
      logout,
      refreshUser,
      unlockWithBiometric,
      setBiometricEnabled,
    ],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthContextValue {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
}
