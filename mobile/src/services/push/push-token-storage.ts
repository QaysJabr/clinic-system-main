import * as SecureStore from 'expo-secure-store';

const PUSH_TOKEN_KEY = 'clinic_push_token';

let memoryPushToken: string | null = null;

export async function getStoredPushToken(): Promise<string | null> {
  if (memoryPushToken) {
    return memoryPushToken;
  }

  try {
    memoryPushToken = await SecureStore.getItemAsync(PUSH_TOKEN_KEY);
  } catch {
    memoryPushToken = null;
  }

  return memoryPushToken;
}

export async function setStoredPushToken(token: string): Promise<void> {
  memoryPushToken = token;
  try {
    await SecureStore.setItemAsync(PUSH_TOKEN_KEY, token);
  } catch {
    // Web / unsupported — memory only.
  }
}

export async function clearStoredPushToken(): Promise<void> {
  memoryPushToken = null;
  try {
    await SecureStore.deleteItemAsync(PUSH_TOKEN_KEY);
  } catch {
    // ignore
  }
}
