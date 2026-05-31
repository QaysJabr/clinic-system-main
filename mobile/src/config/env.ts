import { Platform } from 'react-native';
import Constants from 'expo-constants';

/** Production VPS — always used in release APK (not localhost:8000). */
export const PRODUCTION_API_BASE_URL = 'http://31.97.61.205/api/v1';

function normalizeBaseUrl(url: string): string {
  return url.replace(/\/$/, '');
}

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

  return normalizeBaseUrl(`http://${host}:8000/api/v1`);
}

const defaultHost = Platform.OS === 'android' ? '10.0.2.2' : '127.0.0.1';

function resolveApiBaseUrl(): string {
  // Release APK / production: always VPS — browser works, app must use same host.
  if (!__DEV__) {
    return PRODUCTION_API_BASE_URL;
  }

  const configured =
    (Constants.expoConfig?.extra as { apiBaseUrl?: string } | undefined)?.apiBaseUrl ??
    process.env.EXPO_PUBLIC_API_URL ??
    null;

  if (configured) {
    return normalizeBaseUrl(configured);
  }

  return normalizeBaseUrl(
    resolveDevApiBaseUrl() ?? `http://${defaultHost}:8000/api/v1`,
  );
}

export const env = {
  apiBaseUrl: resolveApiBaseUrl(),
  defaultLocale: 'ar' as const,
  appVersion: Constants.expoConfig?.version ?? '1.0.0',
};
