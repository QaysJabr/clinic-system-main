import { Platform } from 'react-native';
import Constants from 'expo-constants';
import * as Device from 'expo-device';
import { fetchMeta } from '@/api/services/auth.service';
import { pushPlatform, registerPushToken, unregisterPushToken } from '@/api/services/push.service';
import {
  clearStoredPushToken,
  getStoredPushToken,
  setStoredPushToken,
} from '@/services/push/push-token-storage';
import {
  getPushNotificationsEnabled,
  setPushNotificationsEnabled,
} from '@/services/push/push-prefs';
import { isNativePushSupported } from '@/services/push/expo-go';
import { env } from '@/config/env';

type NotificationsModule = typeof import('expo-notifications');

export type PushStatus =
  | 'unsupported_expo_go'
  | 'unsupported_simulator'
  | 'server_disabled'
  | 'permission_denied'
  | 'disabled_by_user'
  | 'registered'
  | 'ready';

export interface PushStatusSnapshot {
  status: PushStatus;
  serverEnabled: boolean;
  userEnabled: boolean;
  hasToken: boolean;
  label: string;
  hint: string;
}

async function loadNotificationsModule(): Promise<NotificationsModule | null> {
  if (!isNativePushSupported()) {
    return null;
  }

  try {
    return await import('expo-notifications');
  } catch {
    return null;
  }
}

async function ensureNotificationHandler(Notifications: NotificationsModule): Promise<void> {
  Notifications.setNotificationHandler({
    handleNotification: async () => ({
      shouldShowAlert: true,
      shouldPlaySound: true,
      shouldSetBadge: true,
      shouldShowBanner: true,
      shouldShowList: true,
    }),
  });
}

async function ensureAndroidChannel(Notifications: NotificationsModule): Promise<void> {
  if (Platform.OS !== 'android') {
    return;
  }

  await Notifications.setNotificationChannelAsync('clinic_default', {
    name: 'إشعارات العيادة',
    importance: Notifications.AndroidImportance.HIGH,
    vibrationPattern: [0, 250, 250, 250],
    lightColor: '#0F4C81',
  });
}

export async function isPushEnabledOnServer(): Promise<boolean> {
  try {
    const meta = await fetchMeta();
    return meta.push.enabled;
  } catch {
    return false;
  }
}

async function getPermissionGranted(): Promise<boolean | null> {
  const Notifications = await loadNotificationsModule();
  if (!Notifications) {
    return null;
  }

  const { status } = await Notifications.getPermissionsAsync();
  return status === 'granted';
}

export async function obtainDevicePushToken(requestPermission = true): Promise<string | null> {
  if (!isNativePushSupported() || !Device.isDevice) {
    return null;
  }

  const Notifications = await loadNotificationsModule();
  if (!Notifications) {
    return null;
  }

  await ensureNotificationHandler(Notifications);

  const { status: existing } = await Notifications.getPermissionsAsync();
  let finalStatus = existing;

  if (existing !== 'granted' && requestPermission) {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') {
    return null;
  }

  await ensureAndroidChannel(Notifications);

  try {
    const token = await Notifications.getDevicePushTokenAsync();
    return token.data;
  } catch {
    return null;
  }
}

export async function getPushStatus(): Promise<PushStatusSnapshot> {
  const userEnabled = await getPushNotificationsEnabled();
  const serverEnabled = await isPushEnabledOnServer();
  const storedToken = await getStoredPushToken();

  if (!isNativePushSupported()) {
    return {
      status: 'unsupported_expo_go',
      serverEnabled,
      userEnabled,
      hasToken: Boolean(storedToken),
      label: 'يتطلب APK',
      hint: 'الإشعار الفوري لا يعمل داخل Expo Go. ثبّت build التطبيق (APK).',
    };
  }

  if (!Device.isDevice) {
    return {
      status: 'unsupported_simulator',
      serverEnabled,
      userEnabled,
      hasToken: Boolean(storedToken),
      label: 'محاكي فقط',
      hint: 'Push يعمل على جهاز حقيقي وليس المحاكي.',
    };
  }

  if (!serverEnabled) {
    return {
      status: 'server_disabled',
      serverEnabled,
      userEnabled,
      hasToken: Boolean(storedToken),
      label: 'الخادم غير مهيّأ',
      hint: 'فعّل PUSH_ENABLED و Firebase credentials على Laravel.',
    };
  }

  if (!userEnabled) {
    return {
      status: 'disabled_by_user',
      serverEnabled,
      userEnabled,
      hasToken: Boolean(storedToken),
      label: 'معطّل',
      hint: 'فعّل الإشعارات من هنا لتصلك التنبيهات الفورية.',
    };
  }

  const permissionGranted = await getPermissionGranted();
  if (permissionGranted === false) {
    return {
      status: 'permission_denied',
      serverEnabled,
      userEnabled,
      hasToken: Boolean(storedToken),
      label: 'الإذن مرفوض',
      hint: 'افتح إعدادات الجوال واسمح بالإشعارات لتطبيق Clinic System.',
    };
  }

  if (storedToken) {
    return {
      status: 'registered',
      serverEnabled,
      userEnabled,
      hasToken: true,
      label: 'مفعّل',
      hint: 'الجهاز مسجّل لاستقبال إشعارات FCM.',
    };
  }

  return {
    status: 'ready',
    serverEnabled,
    userEnabled,
    hasToken: false,
    label: 'جاهز للتسجيل',
    hint: 'سجّل الدخول أو فعّل الإشعارات لتسجيل هذا الجهاز.',
  };
}

export async function syncPushRegistration(): Promise<boolean> {
  if (!isNativePushSupported()) {
    return false;
  }

  const userEnabled = await getPushNotificationsEnabled();
  if (!userEnabled) {
    return false;
  }

  const enabled = await isPushEnabledOnServer();
  if (!enabled) {
    return false;
  }

  const token = await obtainDevicePushToken(false);
  if (!token) {
    const granted = await getPermissionGranted();
    if (granted === false) {
      return false;
    }
    const requested = await obtainDevicePushToken(true);
    if (!requested) {
      return false;
    }
    await registerPushToken({
      token: requested,
      platform: pushPlatform(),
      device_name: Device.deviceName ?? Constants.deviceName ?? 'Clinic Mobile',
      app_version: env.appVersion,
    });
    await setStoredPushToken(requested);
    return true;
  }

  await registerPushToken({
    token,
    platform: pushPlatform(),
    device_name: Device.deviceName ?? Constants.deviceName ?? 'Clinic Mobile',
    app_version: env.appVersion,
  });

  await setStoredPushToken(token);
  return true;
}

export async function enablePushNotifications(): Promise<boolean> {
  await setPushNotificationsEnabled(true);
  return syncPushRegistration();
}

export async function disablePushNotifications(): Promise<void> {
  await setPushNotificationsEnabled(false);
  await syncPushUnregistration();
}

export async function syncPushUnregistration(): Promise<void> {
  const token = await getStoredPushToken();
  if (token) {
    try {
      await unregisterPushToken(token);
    } catch {
      // Server may already have removed it.
    }
  }
  await clearStoredPushToken();
}

export function extractNotificationData(
  notification: import('expo-notifications').Notification,
): Record<string, unknown> | undefined {
  const content = notification.request.content;
  return (content.data as Record<string, unknown> | undefined) ?? undefined;
}

export async function getInitialNotificationData(): Promise<Record<string, unknown> | undefined> {
  const Notifications = await loadNotificationsModule();
  if (!Notifications) {
    return undefined;
  }

  const response = await Notifications.getLastNotificationResponseAsync();
  if (!response) {
    return undefined;
  }

  return extractNotificationData(response.notification);
}

export async function subscribeToNotificationEvents(
  onReceived: () => void,
  onResponse: (data: Record<string, unknown> | undefined) => void,
): Promise<(() => void) | null> {
  const Notifications = await loadNotificationsModule();
  if (!Notifications) {
    return null;
  }

  await ensureNotificationHandler(Notifications);

  const received = Notifications.addNotificationReceivedListener(onReceived);
  const response = Notifications.addNotificationResponseReceivedListener((event) => {
    onResponse(extractNotificationData(event.notification));
  });

  return () => {
    received.remove();
    response.remove();
  };
}
