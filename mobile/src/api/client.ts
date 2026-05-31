import axios, { type AxiosError } from 'axios';
import { env } from '@/config/env';
import { clearToken, getToken } from '@/auth/token-storage';
import type { ApiErrorBody } from '@/types/api';

type UnauthorizedHandler = () => void;

let onUnauthorized: UnauthorizedHandler | null = null;

export function setUnauthorizedHandler(handler: UnauthorizedHandler | null): void {
  onUnauthorized = handler;
}

export const apiClient = axios.create({
  baseURL: env.apiBaseUrl,
  timeout: 20000,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'Accept-Language': env.defaultLocale,
  },
});

apiClient.interceptors.request.use(async (config) => {
  const token = await getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<ApiErrorBody>) => {
    if (error.response?.status === 401) {
      await clearToken();
      onUnauthorized?.();
    }
    return Promise.reject(error);
  },
);

export function unwrapApiError(error: unknown): ApiErrorBody {
  if (axios.isAxiosError(error)) {
    return error.response?.data ?? { message: error.message };
  }
  return { message: 'Unknown error' };
}
