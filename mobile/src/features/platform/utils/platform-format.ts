export interface PlatformClinicFilters {
  q?: string;
  is_active?: '0' | '1';
  subscription_status?: 'active' | 'expired' | 'expiring_soon';
}

export interface PlatformPaymentExportFilters {
  paid_from?: string;
  paid_to?: string;
  clinic_id?: number;
}

export function paymentSourceLabel(source: string): string {
  if (source === 'stripe') {
    return 'Stripe';
  }
  if (source === 'manual') {
    return 'يدوي';
  }
  return source;
}

export function formatPlatformDateTime(iso: string | null | undefined): string {
  if (!iso) {
    return '—';
  }
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) {
    return iso.slice(0, 16).replace('T', ' ');
  }
  return date.toLocaleString('ar', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}
