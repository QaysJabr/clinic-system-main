import { apiClient } from '@/api/client';
import type { ApiResponse, Doctor } from '@/types/api';

export async function fetchDoctors(q?: string): Promise<Doctor[]> {
  const { data } = await apiClient.get<ApiResponse<Doctor[]>>('/doctors', {
    params: q ? { q } : undefined,
  });
  return data.data;
}
