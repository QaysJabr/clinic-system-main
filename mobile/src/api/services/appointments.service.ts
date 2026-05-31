import { apiClient } from '@/api/client';
import type { ApiResponse, Appointment, AppointmentPayload, PaginationMeta } from '@/types/api';

export interface AppointmentsListResult {
  items: Appointment[];
  meta: PaginationMeta;
}

export async function fetchAppointments(params?: {
  date?: string;
  per_page?: number;
  page?: number;
}): Promise<AppointmentsListResult> {
  const { data } = await apiClient.get<ApiResponse<Appointment[]>>('/appointments', { params });
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

export async function fetchAppointment(id: number): Promise<Appointment> {
  const { data } = await apiClient.get<ApiResponse<Appointment>>(`/appointments/${id}`);
  return data.data;
}

export async function createAppointment(payload: AppointmentPayload): Promise<Appointment> {
  const { data } = await apiClient.post<ApiResponse<Appointment>>('/appointments', payload);
  return data.data;
}

export async function updateAppointment(id: number, payload: AppointmentPayload): Promise<Appointment> {
  const { data } = await apiClient.put<ApiResponse<Appointment>>(`/appointments/${id}`, payload);
  return data.data;
}

export async function checkInAppointment(id: number): Promise<{ appointment: Appointment; visit: import('@/types/api').Visit }> {
  const { data } = await apiClient.post<ApiResponse<{ appointment: Appointment; visit: import('@/types/api').Visit }>>(
    `/appointments/${id}/check-in`,
  );
  return data.data;
}
