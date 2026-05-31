const BIOMETRIC_KEY = 'clinic_biometric_enabled';

async function getSecureStore() {
  return import('expo-secure-store');
}

export async function getBiometricEnabled(): Promise<boolean> {
  try {
    const SecureStore = await getSecureStore();
    return (await SecureStore.getItemAsync(BIOMETRIC_KEY)) === '1';
  } catch {
    return false;
  }
}

export async function setBiometricEnabled(enabled: boolean): Promise<void> {
  const SecureStore = await getSecureStore();
  if (enabled) {
    await SecureStore.setItemAsync(BIOMETRIC_KEY, '1');
  } else {
    await SecureStore.deleteItemAsync(BIOMETRIC_KEY);
  }
}
