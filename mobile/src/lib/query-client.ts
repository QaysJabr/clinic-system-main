import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30_000,
      gcTime: 24 * 60 * 60_000,
      networkMode: 'offlineFirst',
    },
  },
});

export const queryKeys = {
  meta: ['meta'] as const,
  me: ['auth', 'me'] as const,
  dashboard: ['dashboard'] as const,
  invoices: (filters?: Record<string, string | number | undefined>) => ['invoices', filters ?? {}] as const,
  invoice: (id: number) => ['invoices', id] as const,
  appointments: (date?: string) => ['appointments', { date }] as const,
  appointment: (id: number) => ['appointments', id] as const,
  visits: (date?: string, status?: string) => ['visits', { date, status }] as const,
  visit: (id: number) => ['visits', id] as const,
  notifications: (page?: number) => ['notifications', { page }] as const,
  patients: (q: string) => ['patients', { q }] as const,
  patient: (id: number) => ['patients', id] as const,
  doctors: ['doctors'] as const,
  platformDashboard: ['platform', 'dashboard'] as const,
  platformClinics: (filters?: Record<string, string | number | undefined>) =>
    ['platform', 'clinics', filters ?? {}] as const,
  platformClinic: (id: number, paymentFilters?: Record<string, string | undefined>) =>
    ['platform', 'clinics', id, paymentFilters ?? {}] as const,
  platformPlans: ['platform', 'plans'] as const,
  platformPlan: (id: number) => ['platform', 'plans', id] as const,
  platformNotifications: (page?: number) => ['platform', 'notifications', { page }] as const,
};
