import * as Haptics from 'expo-haptics';
import { Platform } from 'react-native';

export async function hapticLight(): Promise<void> {
  if (Platform.OS === 'web') {
    return;
  }
  try {
    await Haptics.impactAsync(Haptics.ImpactFeedbackStyle.Light);
  } catch {
    // Non-blocking.
  }
}

export async function hapticSuccess(): Promise<void> {
  if (Platform.OS === 'web') {
    return;
  }
  try {
    await Haptics.notificationAsync(Haptics.NotificationFeedbackType.Success);
  } catch {
    // Non-blocking.
  }
}

export async function hapticError(): Promise<void> {
  if (Platform.OS === 'web') {
    return;
  }
  try {
    await Haptics.notificationAsync(Haptics.NotificationFeedbackType.Error);
  } catch {
    // Non-blocking.
  }
}

export function fireHapticLight(): void {
  void hapticLight();
}

export function fireHapticSuccess(): void {
  void hapticSuccess();
}

export function fireHapticError(): void {
  void hapticError();
}
