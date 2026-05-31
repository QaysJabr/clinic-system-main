import { Platform } from 'react-native';
import Constants from 'expo-constants';

function resolveDevApiBaseUrl(): string | null {
  const debuggerHost =
    Constants.expoGoConfig?.debuggerHost ??
    Constants.expoConfig?.hostUri ??
    null;

  if (!debuggerHost) {
    return null;
  }

  const host = debuggerHost.split(':')[0];
  if (!host) {
    return null;
  }

  return `http://${host}:8000/api/v1`;
}

const defaultHost = Platform.OS === 'android' ? '10.0.2.2' : '127.0.0.1';

const configuredApiBaseUrl =
  (Constants.expoConfig?.extra as { apiBaseUrl?: string } | undefined)?.apiBaseUrl ??
  process.env.EXPO_PUBLIC_API_URL ??
  null;

export const env = {
  apiBaseUrl:
    configuredApiBaseUrl ??
    (__DEV__ ? resolveDevApiBaseUrl() : null) ??
    `http://${defaultHost}:8000/api/v1`,
  defaultLocale: 'ar' as const,
  appVersion: Constants.expoConfig?.version ?? '1.0.0',
};
