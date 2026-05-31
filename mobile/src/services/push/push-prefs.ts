import AsyncStorage from '@react-native-async-storage/async-storage';

const PUSH_ENABLED_KEY = 'clinic_push_enabled';

export async function getPushNotificationsEnabled(): Promise<boolean> {
  try {
    const value = await AsyncStorage.getItem(PUSH_ENABLED_KEY);
    if (value === null) {
      return true;
    }
    return value === '1';
  } catch {
    return true;
  }
}

export async function setPushNotificationsEnabled(enabled: boolean): Promise<void> {
  await AsyncStorage.setItem(PUSH_ENABLED_KEY, enabled ? '1' : '0');
}
