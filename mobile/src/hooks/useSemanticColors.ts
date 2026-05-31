import { useMemo } from 'react';
import type { AppTheme } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { AppointmentStatus, InvoiceStatus, VisitStatus } from '@/types/api';

function buildSemanticColors(colors: AppTheme['colors']) {
  const appointment: Record<AppointmentStatus, string> = {
    scheduled: colors.info,
    confirmed: colors.brand,
    checked_in: colors.brandSecondary,
    in_progress: colors.warning,
    completed: colors.success,
    cancelled: colors.textMuted,
    no_show: colors.danger,
  };

  const invoice: Record<InvoiceStatus, string> = {
    unpaid: colors.danger,
    partial: colors.warning,
    paid: colors.success,
  };

  const visit: Record<VisitStatus, string> = {
    waiting: colors.warning,
    in_progress: colors.info,
    completed: colors.success,
    cancelled: colors.textMuted,
  };

  return {
    appointment,
    invoice,
    visit,
    paymentSource: (source: string) => {
      if (source === 'stripe') {
        return colors.info;
      }
      if (source === 'manual') {
        return colors.success;
      }
      return colors.textMuted;
    },
  };
}

export function useSemanticColors() {
  const { theme } = useAppTheme();
  return useMemo(() => buildSemanticColors(theme.colors), [theme.colors]);
}
