export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface ApiResponse<T> {
  data: T;
  meta?: PaginationMeta;
  message?: string;
}

export interface ApiErrorBody {
  message?: string;
  errors?: Record<string, string[]>;
  meta?: {
    two_factor_required?: boolean;
  };
}

export interface Clinic {
  id: number;
  name: string;
  slug?: string;
  currency?: string;
  logo_url?: string | null;
}

export interface User {
  id: number;
  name: string;
  email: string;
  avatar_url: string | null;
  clinic_id: number | null;
  persona?: UserPersona;
  roles: string[];
  permissions: string[];
  clinic?: Clinic;
}

export type UserPersona = 'platform' | 'clinic';

export interface LoginPayload {
  email: string;
  password: string;
  device_name?: string;
}

export interface LoginResult {
  token: string;
  token_type: string;
  expires_at: string;
  user: User;
}

export interface DashboardStats {
  today_appointments: number;
  today_visits: number;
  today_visits_waiting: number;
  open_invoices: number;
}

export interface DashboardFinancial {
  today_cash_collected: number;
  accounts_receivable: number;
  today_net_cash_flow: number;
}

export interface DashboardDoctorEarning {
  total: number;
  pending: number;
  paid: number;
}

export interface DashboardData {
  persona: ClinicPersona;
  persona_label: string;
  currency: string;
  clinic: {
    name: string;
    logo_url: string | null;
  };
  stats: DashboardStats;
  today_appointments: Appointment[];
  visit_queue: Visit[];
  alerts: DashboardAlert[];
  financial?: DashboardFinancial | null;
  doctor_earning?: DashboardDoctorEarning | null;
  recent_invoices: Invoice[];
  recent_payments: ClinicPayment[];
}

export type ClinicPersona = 'admin' | 'doctor' | 'receptionist' | 'accountant';

export interface DashboardAlert {
  type?: 'warning' | 'info' | 'danger';
  title?: string;
  message?: string;
}

export interface PersonRef {
  id: number;
  full_name: string;
}

export interface Appointment {
  id: number;
  patient_id: number;
  doctor_id: number;
  appointment_date: string;
  start_time: string;
  end_time: string;
  status: AppointmentStatus;
  reason: string | null;
  notes: string | null;
  visit_id?: number | null;
  patient?: PersonRef;
  doctor?: PersonRef;
}

export type AppointmentStatus =
  | 'scheduled'
  | 'confirmed'
  | 'checked_in'
  | 'in_progress'
  | 'completed'
  | 'cancelled'
  | 'no_show';

export interface Visit {
  id: number;
  patient_id: number;
  doctor_id: number;
  appointment_id: number | null;
  visit_date: string;
  status: VisitStatus;
  chief_complaint: string | null;
  diagnosis: string | null;
  treatment_plan: string | null;
  notes: string | null;
  patient?: PersonRef & { file_number?: string | null };
  doctor?: PersonRef;
}

export interface VisitClinicalPayload {
  chief_complaint?: string | null;
  diagnosis?: string | null;
  treatment_plan?: string | null;
  notes?: string | null;
}

export type VisitStatus = 'waiting' | 'in_progress' | 'completed' | 'cancelled';

export interface Doctor {
  id: number;
  full_name: string;
  status: string;
}

export type InvoiceStatus = 'unpaid' | 'partial' | 'paid';

export interface Invoice {
  id: number;
  invoice_number: string;
  patient_id: number;
  visit_id: number | null;
  doctor_id: number | null;
  total: string;
  paid: string;
  remaining: string;
  status: InvoiceStatus;
  due_date: string | null;
  created_at: string | null;
  notes: string | null;
  patient?: PersonRef;
  doctor?: PersonRef;
}

export interface ClinicPayment {
  id: number;
  invoice_id: number;
  amount: string;
  payment_method: string;
  payment_date: string | null;
  notes: string | null;
  created_at: string | null;
}

export interface RecordPaymentPayload {
  invoice_id: number;
  amount: number;
  payment_method: 'cash' | 'card' | 'bank_transfer' | 'other';
  payment_date: string;
  notes?: string | null;
}

export interface Patient {
  id: number;
  file_number?: string | null;
  full_name: string;
  phone: string | null;
  email: string | null;
  date_of_birth: string | null;
  gender: string | null;
  notes?: string | null;
}

export interface PatientPayload {
  full_name: string;
  phone?: string | null;
  gender?: 'male' | 'female' | null;
  date_of_birth?: string | null;
  notes?: string | null;
  file_number?: string | null;
}

export interface AppointmentPayload {
  patient_id: number;
  doctor_id: number;
  appointment_date: string;
  start_time: string;
  end_time?: string | null;
  status: AppointmentStatus;
  reason?: string | null;
  notes?: string | null;
}

export interface AppNotification {
  id: number;
  type: string;
  type_label: string;
  title: string;
  message: string;
  is_read: boolean;
  created_at: string;
}

export interface AppMeta {
  api_version: string;
  min_app_version: string;
  app_name: string;
  locales: string[];
  default_locale: string;
  auth: {
    type: string;
    header: string;
    two_factor_supported: boolean;
  };
  push: {
    enabled: boolean;
    register_path: string;
    platforms: string[];
  };
}

export interface PlatformDashboardData {
  total_revenue: number;
  monthly_revenue: number;
  monthly_revenue_manual: number;
  monthly_revenue_stripe: number;
  total_clinics: number;
  new_clinics_this_month: number;
  active_clinics: number;
  expired_clinics: number;
  suspended_clinics: number;
  expiring_soon_count: number;
  expiring_soon_days: number;
  revenue_by_month: Array<{
    label: string;
    amount: number;
    manual: number;
    stripe: number;
  }>;
  recent_payments: SubscriptionPayment[];
  clinics_expiring_soon: PlatformClinic[];
  clinics_expired: PlatformClinic[];
}

export interface PlatformClinic {
  id: number;
  name: string;
  is_active: boolean;
  subscription_status: string;
  subscription_expires_at: string | null;
  subscription_tier: 'active' | 'expiring' | 'expired' | 'suspended';
  subscription_tier_label: string;
  plan_id: number | null;
  plan?: { id: number; name: string } | null;
  owner?: { id: number; name: string; email: string } | null;
  total_paid?: number | null;
  created_at?: string | null;
  stripe_status_label?: string | null;
}

export interface PlatformPlan {
  id: number;
  name: string;
  slug: string;
  price_monthly: string;
  price_yearly: string;
  display_monthly: string;
  display_yearly: string;
  max_patients: number | null;
  max_users: number | null;
  features: string[];
  trial_days: number;
  stripe_price_id: string | null;
  stripe_price_yearly_id: string | null;
  is_active: boolean;
  sort_order: number;
  clinics_count: number;
}

export interface PlatformPlanPayload {
  name: string;
  slug: string;
  price_monthly: number;
  price_yearly: number;
  max_patients?: number | null;
  max_users?: number | null;
  features?: string[];
  trial_days?: number;
  sort_order?: number;
  is_active?: boolean;
  stripe_price_id?: string | null;
  stripe_price_yearly_id?: string | null;
}

export interface SubscriptionPayment {
  id: number;
  clinic_id: number;
  amount: string;
  paid_at: string | null;
  status: string;
  source: string;
  notes: string | null;
  clinic?: { id: number; name: string } | null;
}

export interface PlatformClinicDetail {
  clinic: PlatformClinic;
  recent_payments: SubscriptionPayment[];
  plans: PlatformPlan[];
}

export interface PlatformClinicsListResult {
  items: PlatformClinic[];
  meta: PaginationMeta;
}
