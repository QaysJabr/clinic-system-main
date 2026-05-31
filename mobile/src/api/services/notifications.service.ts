import { apiClient } from '@/api/client';
import type { ApiResponse, AppNotification, PaginationMeta } from '@/types/api';

export interface NotificationsListResult {
  items: AppNotification[];
  meta: PaginationMeta & { unread_count: number };
}

export async function fetchNotifications(params?: {
  per_page?: number;
  page?: number;
}): Promise<NotificationsListResult> {
  const { data } = await apiClient.get<ApiResponse<AppNotification[]>>('/notifications', { params });
  return {
    items: data.data,
    meta: {
      current_page: data.meta?.current_page ?? 1,
      last_page: data.meta?.last_page ?? 1,
      per_page: data.meta?.per_page ?? 20,
      total: data.meta?.total ?? data.data.length,
      unread_count: (data.meta as { unread_count?: number } | undefined)?.unread_count ?? 0,
    },
  };
}

export async function markNotificationRead(id: number): Promise<AppNotification> {
  const { data } = await apiClient.post<ApiResponse<AppNotification>>(`/notifications/${id}/read`);
  return data.data;
}
