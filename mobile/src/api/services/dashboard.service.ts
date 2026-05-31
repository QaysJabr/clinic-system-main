import { apiClient } from '@/api/client';
import type { ApiResponse, DashboardData } from '@/types/api';

export async function fetchDashboard(): Promise<DashboardData> {
  const { data } = await apiClient.get<ApiResponse<DashboardData>>('/dashboard');
  return data.data;
}
