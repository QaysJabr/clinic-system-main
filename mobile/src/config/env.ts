import Constants from 'expo-constants';

/** Fallback when neither app.config extra nor EXPO_PUBLIC_API_URL is set (e.g. old builds). */
const FALLBACK_API_BASE_URL = 'http://31.97.61.205/api/v1';

function resolveApiBaseUrl(): string {
  const fromExpo = Constants.expoConfig?.extra?.apiBaseUrl;
  if (typeof fromExpo === 'string' && fromExpo.trim() !== '') {
    return fromExpo.trim().replace(/\/$/, '');
  }

  const fromPublic = process.env.EXPO_PUBLIC_API_URL;
  if (typeof fromPublic === 'string' && fromPublic.trim() !== '') {
    return fromPublic.trim().replace(/\/$/, '');
  }

  return FALLBACK_API_BASE_URL;
}

export const env = {
  apiBaseUrl: resolveApiBaseUrl(),
  defaultLocale: 'ar' as const,
  appVersion: Constants.expoConfig?.version ?? '1.0.0',
};
