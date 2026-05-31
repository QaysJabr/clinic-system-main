const appJson = require('./app.json');

const PRODUCTION_API_BASE_URL = 'http://31.97.61.205/api/v1';

const apiBaseUrl =
  process.env.EXPO_PUBLIC_API_URL ??
  appJson.expo.extra?.apiBaseUrl ??
  PRODUCTION_API_BASE_URL;

module.exports = {
  expo: {
    ...appJson.expo,
    extra: {
      ...appJson.expo.extra,
      apiBaseUrl,
    },
    android: {
      ...appJson.expo.android,
      googleServicesFile:
        process.env.GOOGLE_SERVICES_JSON ?? './google-services.json',
      usesCleartextTraffic: true,
    },
    plugins: [
      ...(appJson.expo.plugins ?? []),
      [
        'expo-build-properties',
        {
          android: {
            usesCleartextTraffic: true,
          },
        },
      ],
    ],
  },
};
