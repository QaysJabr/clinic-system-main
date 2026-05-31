import { apiClient } from '@/api/client';
import type { ApiResponse, PaginationMeta, Visit, VisitStatus } from '@/types/api';

export interface VisitsListResult {
  items: Visit[];
  meta: PaginationMeta;
}

export async function fetchVisits(params?: {
  date?: string;
  status?: VisitStatus;
  per_page?: number;
  page?: number;
}): Promise<VisitsListResult> {
  const { data } = await apiClient.get<ApiResponse<Visit[]>>('/visits', { params });
  return {
    items: data.data,
    meta: data.meta ?? {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: data.data.length,
    },
  };
}

export async function fetchVisit(id: number): Promise<Visit> {
  const { data } = await apiClient.get<ApiResponse<Visit>>(`/visits/${id}`);
  return data.data;
}

export async function updateVisitStatus(id: number, status: VisitStatus): Promise<Visit> {
  const { data } = await apiClient.patch<ApiResponse<Visit>>(`/visits/${id}/status`, { status });
  return data.data;
}

export async function updateVisitClinical(id: number, payload: import('@/types/api').VisitClinicalPayload): Promise<Visit> {
  const { data } = await apiClient.put<ApiResponse<Visit>>(`/visits/${id}`, payload);
  return data.data;
}
