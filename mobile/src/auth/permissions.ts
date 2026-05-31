import type { User } from '@/types/api';

export const PERMS = {
  MANAGE_PATIENTS: 'manage patients',
  MANAGE_APPOINTMENTS: 'manage appointments',
  MANAGE_VISITS: 'manage visits',
  MANAGE_INVOICES: 'manage invoices',
  MANAGE_PAYMENTS: 'manage payments',
  VIEW_REPORTS: 'view reports',
  VIEW_DOCTOR_EARNINGS: 'view doctor earnings',
} as const;

export type ClinicPersona = 'admin' | 'doctor' | 'receptionist' | 'accountant';

const personaLabels: Record<ClinicPersona, string> = {
  admin: 'مدير العيادة',
  doctor: 'طبيب',
  receptionist: 'استقبال',
  accountant: 'محاسب',
};

export function can(user: User | null | undefined, permission: string): boolean {
  return Boolean(user?.permissions?.includes(permission));
}

export function resolveClinicPersona(user: User | null | undefined): ClinicPersona {
  if (!user) {
    return 'receptionist';
  }
  if (user.roles.includes('admin')) {
    return 'admin';
  }
  if (user.roles.includes('accountant')) {
    return 'accountant';
  }
  if (user.roles.includes('receptionist')) {
    return 'receptionist';
  }
  if (user.roles.includes('doctor')) {
    return 'doctor';
  }
  return 'admin';
}

export function clinicPersonaLabel(user: User | null | undefined, apiLabel?: string): string {
  if (apiLabel?.trim()) {
    return apiLabel;
  }
  return personaLabels[resolveClinicPersona(user)];
}

export function showAppointmentsTab(user: User | null | undefined, persona: ClinicPersona): boolean {
  if (!can(user, PERMS.MANAGE_APPOINTMENTS)) {
    return false;
  }
  return persona !== 'accountant';
}

export function showVisitsTab(user: User | null | undefined, persona: ClinicPersona): boolean {
  if (!can(user, PERMS.MANAGE_VISITS)) {
    return false;
  }
  return persona === 'doctor' || persona === 'receptionist' || persona === 'admin';
}

export function showFinanceTab(user: User | null | undefined): boolean {
  return can(user, PERMS.MANAGE_INVOICES);
}

export function canCheckInAppointment(status: string, visitId?: number | null): boolean {
  if (visitId) {
    return false;
  }
  return status === 'scheduled' || status === 'confirmed';
}

export function canRecordPayment(user: User | null | undefined): boolean {
  return can(user, PERMS.MANAGE_PAYMENTS);
}
