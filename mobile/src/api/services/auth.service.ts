import { apiClient } from '@/api/client';
import type { ApiResponse, LoginPayload, LoginResult, User } from '@/types/api';

export async function login(payload: LoginPayload): Promise<LoginResult> {
  const { data } = await apiClient.post<ApiResponse<LoginResult>>('/auth/login', payload);
  return data.data;
}

export async function logout(fcmToken?: string): Promise<void> {
  await apiClient.post('/auth/logout', fcmToken ? { fcm_token: fcmToken } : undefined);
}

export async function fetchCurrentUser(): Promise<User> {
  const { data } = await apiClient.get<ApiResponse<User>>('/auth/me');
  return data.data;
}

export async function fetchMeta() {
  const { data } = await apiClient.get<ApiResponse<import('@/types/api').AppMeta>>('/meta');
  return data.data;
}
