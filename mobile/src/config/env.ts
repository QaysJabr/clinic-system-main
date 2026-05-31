import Constants from 'expo-constants';

/** Production VPS — hardcoded; mobile app always talks to the live server. */
export const PRODUCTION_API_BASE_URL = 'http://31.97.61.205/api/v1';

export const env = {
  apiBaseUrl: PRODUCTION_API_BASE_URL,
  defaultLocale: 'ar' as const,
  appVersion: Constants.expoConfig?.version ?? '1.0.0',
};
