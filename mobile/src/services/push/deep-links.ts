import type { Href } from 'expo-router';

export interface PushPayload {
  notification_id?: string;
  type?: string;
  related_type?: string;
  related_id?: string;
}

export function parsePushPayload(data: Record<string, unknown> | undefined): PushPayload {
  if (!data) {
    return {};
  }

  return {
    notification_id: data.notification_id != null ? String(data.notification_id) : undefined,
    type: data.type != null ? String(data.type) : undefined,
    related_type: data.related_type != null ? String(data.related_type) : undefined,
    related_id: data.related_id != null ? String(data.related_id) : undefined,
  };
}

export function resolvePushRoute(payload: PushPayload, scope: 'clinic' | 'platform' = 'clinic'): Href {
  if (scope === 'platform') {
    return '/(platform)/notifications' as Href;
  }

  const relatedType = (payload.related_type ?? '').toLowerCase();
  const relatedId = payload.related_id ?? '';
  const type = payload.type ?? '';

  if (relatedType === 'appointment' && relatedId) {
    return `/(app)/appointments/${relatedId}` as Href;
  }

  if (type.includes('appointment') && relatedId) {
    return `/(app)/appointments/${relatedId}` as Href;
  }

  if (type === 'visit_recorded' || relatedType === 'visit') {
    return '/(app)/visits' as Href;
  }

  return '/(app)/notifications' as Href;
}
