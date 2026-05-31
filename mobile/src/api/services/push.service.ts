import { Platform } from 'react-native';
import { apiClient } from '@/api/client';
import type { ApiResponse } from '@/types/api';

export interface PushRegisterResult {
  id: number;
  platform: 'android' | 'ios';
  registered_at: string;
}

export async function registerPushToken(payload: {
  token: string;
  platform: 'android' | 'ios';
  device_name?: string;
  app_version?: string;
}): Promise<PushRegisterResult> {
  const { data } = await apiClient.post<ApiResponse<PushRegisterResult>>('/push/register', payload);
  return data.data;
}

export async function unregisterPushToken(token?: string): Promise<number> {
  const { data } = await apiClient.post<ApiResponse<{ deleted: number }>>('/push/unregister', {
    token: token ?? undefined,
  });
  return data.data.deleted;
}

export function pushPlatform(): 'android' | 'ios' {
  return Platform.OS === 'ios' ? 'ios' : 'android';
}
