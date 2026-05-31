import { apiClient } from '@/api/client';
import { downloadAndShareCsv, type ExportQueryParams } from '@/utils/export-file';
import type {
  PlatformClinicFilters,
  PlatformPaymentExportFilters,
} from '@/features/platform/utils/platform-format';
import type {
  ApiResponse,
  PaginationMeta,
  PlatformClinic,
  PlatformClinicDetail,
  PlatformClinicsListResult,
  PlatformDashboardData,
  PlatformPlan,
  PlatformPlanPayload,
  SubscriptionPayment,
} from '@/types/api';

export async function fetchPlatformDashboard(): Promise<PlatformDashboardData> {
  const { data } = await apiClient.get<ApiResponse<PlatformDashboardData>>('/platform/dashboard');
  return data.data;
}

export async function fetchPlatformClinics(params?: {
  q?: string;
  is_active?: '0' | '1';
  subscription_status?: 'active' | 'expired' | 'expiring_soon';
  page?: number;
  per_page?: number;
}): Promise<PlatformClinicsListResult> {
  const { data } = await apiClient.get<ApiResponse<PlatformClinic[]>>('/platform/clinics', { params });
  return {
    items: data.data,
    meta: data.meta ?? {
      current_page: 1,
      last_page: 1,
      per_page: params?.per_page ?? 20,
      total: data.data.length,
    },
  };
}

export async function fetchPlatformClinic(
  id: number,
  params?: PlatformPaymentExportFilters,
): Promise<PlatformClinicDetail> {
  const { data } = await apiClient.get<ApiResponse<PlatformClinicDetail>>(`/platform/clinics/${id}`, {
    params: {
      paid_from: params?.paid_from || undefined,
      paid_to: params?.paid_to || undefined,
    },
  });
  return data.data;
}

export async function activatePlatformClinic(id: number): Promise<PlatformClinic> {
  const { data } = await apiClient.post<ApiResponse<PlatformClinic>>(`/platform/clinics/${id}/activate`);
  return data.data;
}

export async function suspendPlatformClinic(id: number): Promise<PlatformClinic> {
  const { data } = await apiClient.post<ApiResponse<PlatformClinic>>(`/platform/clinics/${id}/suspend`);
  return data.data;
}

export async function updatePlatformClinic(
  id: number,
  payload: {
    is_active: boolean;
    subscription_status: 'active' | 'expired';
    subscription_expires_at?: string | null;
    plan_id?: number | null;
  },
): Promise<PlatformClinic> {
  const { data } = await apiClient.put<ApiResponse<PlatformClinic>>(`/platform/clinics/${id}`, payload);
  return data.data;
}

export async function recordPlatformClinicPayment(
  id: number,
  payload: {
    amount: number;
    paid_at?: string;
    notes?: string | null;
    subscription_expires_at?: string | null;
  },
): Promise<{ clinic: PlatformClinic; payment: SubscriptionPayment }> {
  const { data } = await apiClient.post<ApiResponse<{ clinic: PlatformClinic; payment: SubscriptionPayment }>>(
    `/platform/clinics/${id}/record-payment`,
    payload,
  );
  return data.data;
}

export async function fetchPlatformPlans(): Promise<PlatformPlan[]> {
  const { data } = await apiClient.get<ApiResponse<PlatformPlan[]>>('/platform/plans');
  return data.data;
}

export async function togglePlatformPlan(id: number): Promise<PlatformPlan> {
  const { data } = await apiClient.post<ApiResponse<PlatformPlan>>(`/platform/plans/${id}/toggle-active`);
  return data.data;
}

export async function createPlatformPlan(payload: PlatformPlanPayload): Promise<PlatformPlan> {
  const { data } = await apiClient.post<ApiResponse<PlatformPlan>>('/platform/plans', payload);
  return data.data;
}

export async function updatePlatformPlan(id: number, payload: PlatformPlanPayload): Promise<PlatformPlan> {
  const { data } = await apiClient.put<ApiResponse<PlatformPlan>>(`/platform/plans/${id}`, payload);
  return data.data;
}

export async function deletePlatformPlan(id: number): Promise<void> {
  await apiClient.delete(`/platform/plans/${id}`);
}

export interface PlatformNotificationsListResult {
  items: import('@/types/api').AppNotification[];
  meta: PaginationMeta & { unread_count?: number };
}

export async function fetchPlatformNotifications(page = 1): Promise<PlatformNotificationsListResult> {
  const { data } = await apiClient.get<ApiResponse<import('@/types/api').AppNotification[]>>('/platform/notifications', {
    params: { page, per_page: 20 },
  });
  return {
    items: data.data,
    meta: (data.meta ?? {
      current_page: 1,
      last_page: 1,
      per_page: 20,
      total: data.data.length,
      unread_count: 0,
    }) as PlatformNotificationsListResult['meta'],
  };
}

export async function markPlatformNotificationRead(id: number): Promise<void> {
  await apiClient.post(`/platform/notifications/${id}/read`);
}

export async function markAllPlatformNotificationsRead(): Promise<void> {
  await apiClient.post('/platform/notifications/read-all');
}

function toExportParams(filters?: PlatformClinicFilters | PlatformPaymentExportFilters): ExportQueryParams {
  if (!filters) {
    return {};
  }
  return { ...filters };
}

export async function exportPlatformClinicsCsv(filters?: PlatformClinicFilters): Promise<void> {
  const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
  await downloadAndShareCsv('/platform/exports/clinics', `platform-clinics-${stamp}.csv`, toExportParams(filters));
}

export async function exportPlatformPaymentsCsv(
  clinicId?: number,
  filters?: PlatformPaymentExportFilters,
): Promise<void> {
  const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
  const path =
    clinicId != null
      ? `/platform/exports/clinics/${clinicId}/payments`
      : '/platform/exports/payments';
  const prefix = clinicId != null ? `clinic-${clinicId}-` : '';
  const params: ExportQueryParams = {
    paid_from: filters?.paid_from,
    paid_to: filters?.paid_to,
  };
  await downloadAndShareCsv(path, `platform-payments-${prefix}${stamp}.csv`, params);
}
