const TOKEN_KEY = 'clinic_auth_token';

let memoryToken: string | null = null;

async function getSecureStore() {
  return import('expo-secure-store');
}

export async function getToken(): Promise<string | null> {
  if (memoryToken) {
    return memoryToken;
  }

  try {
    const SecureStore = await getSecureStore();
    memoryToken = await SecureStore.getItemAsync(TOKEN_KEY);
  } catch {
    memoryToken = null;
  }

  return memoryToken;
}

export async function setToken(token: string): Promise<void> {
  memoryToken = token;
  const SecureStore = await getSecureStore();
  await SecureStore.setItemAsync(TOKEN_KEY, token);
}

export async function clearToken(): Promise<void> {
  memoryToken = null;
  try {
    const SecureStore = await getSecureStore();
    await SecureStore.deleteItemAsync(TOKEN_KEY);
  } catch {
    // Secure store may be unavailable on web; memory token is enough.
  }
}
