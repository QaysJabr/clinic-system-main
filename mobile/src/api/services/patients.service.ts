import { apiClient } from '@/api/client';
import type { ApiResponse, PaginationMeta, Patient, PatientPayload } from '@/types/api';

export interface PatientsListResult {
  items: Patient[];
  meta: PaginationMeta;
}

export async function searchPatients(q: string, perPage = 20): Promise<PatientsListResult> {
  const { data } = await apiClient.get<ApiResponse<Patient[]>>('/patients', {
    params: { q, per_page: perPage },
  });
  return {
    items: data.data,
    meta: data.meta ?? {
      current_page: 1,
      last_page: 1,
      per_page: perPage,
      total: data.data.length,
    },
  };
}

export async function fetchPatient(id: number): Promise<Patient> {
  const { data } = await apiClient.get<ApiResponse<Patient>>(`/patients/${id}`);
  return data.data;
}

export async function createPatient(payload: PatientPayload): Promise<Patient> {
  const { data } = await apiClient.post<ApiResponse<Patient>>('/patients', payload);
  return data.data;
}
