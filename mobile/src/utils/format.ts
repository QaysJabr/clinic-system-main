import type { AppointmentStatus, InvoiceStatus, VisitStatus } from '@/types/api';

export const appointmentStatusLabels: Record<AppointmentStatus, string> = {
  scheduled: 'مجدول',
  confirmed: 'مؤكد',
  checked_in: 'حضر',
  in_progress: 'جاري',
  completed: 'مكتمل',
  cancelled: 'ملغى',
  no_show: 'لم يحضر',
};

export const visitStatusLabels: Record<VisitStatus, string> = {
  waiting: 'بالانتظار',
  in_progress: 'جاري',
  completed: 'مكتمل',
  cancelled: 'ملغى',
};

export const invoiceStatusLabels: Record<InvoiceStatus, string> = {
  unpaid: 'غير مدفوعة',
  partial: 'مدفوعة جزئياً',
  paid: 'مدفوعة',
};

export function formatGender(gender: string | null | undefined): string {
  if (!gender) {
    return 'غير مسجل';
  }
  const map: Record<string, string> = {
    male: 'ذكر',
    female: 'أنثى',
    m: 'ذكر',
    f: 'أنثى',
  };
  return map[gender.toLowerCase()] ?? gender;
}

export function formatApiError(error: unknown, fallback = 'حدث خطأ غير متوقع'): string {
  if (typeof error === 'object' && error !== null && 'response' in error) {
    const response = (error as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const data = response?.data;
    if (data?.message) {
      return data.message;
    }
    if (data?.errors) {
      const first = Object.values(data.errors)[0]?.[0];
      if (first) {
        return first;
      }
    }
  }

  if (error instanceof Error && error.message) {
    if (error.message === 'Network Error') {
      return 'تعذر الاتصال بالخادم. تأكد أن Laravel يعمل على المنفذ 8000 وأن الجوال على نفس الشبكة.';
    }
    return error.message;
  }

  return fallback;
}
