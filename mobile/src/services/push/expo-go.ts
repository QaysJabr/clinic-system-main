import { isRunningInExpoGo } from 'expo';
import Constants, { ExecutionEnvironment } from 'expo-constants';

/** Remote push tokens are not available inside Expo Go (SDK 53+). */
export function isExpoGo(): boolean {
  if (isRunningInExpoGo()) {
    return true;
  }

  // appOwnership is deprecated and often null in newer SDKs.
  return Constants.executionEnvironment === ExecutionEnvironment.StoreClient;
}

export function isNativePushSupported(): boolean {
  return !isExpoGo();
}
