import { apiClient } from '@/api/client';
import type {
  ApiResponse,
  ClinicPayment,
  Invoice,
  PaginationMeta,
  RecordPaymentPayload,
} from '@/types/api';

export interface InvoicesListResult {
  items: Invoice[];
  meta: PaginationMeta;
}

export async function fetchInvoices(params?: {
  status?: 'unpaid' | 'partial' | 'paid';
  patient?: string;
  page?: number;
  per_page?: number;
}): Promise<InvoicesListResult> {
  const { data } = await apiClient.get<ApiResponse<Invoice[]>>('/invoices', { params });
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

export async function fetchInvoice(id: number): Promise<Invoice> {
  const { data } = await apiClient.get<ApiResponse<Invoice>>(`/invoices/${id}`);
  return data.data;
}

export async function recordPayment(payload: RecordPaymentPayload): Promise<ClinicPayment> {
  const { data } = await apiClient.post<ApiResponse<ClinicPayment>>('/payments', payload);
  return data.data;
}
