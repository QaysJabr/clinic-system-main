import type { User } from '@/types/api';

export function isPlatformOwner(user: User | null | undefined): boolean {
  if (!user) {
    return false;
  }
  if (user.persona === 'platform') {
    return true;
  }
  return user.roles.includes('super_admin') && user.clinic_id === null;
}
