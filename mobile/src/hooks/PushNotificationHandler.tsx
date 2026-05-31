import { useEffect, useRef } from 'react';
import { useRouter } from 'expo-router';
import { useQueryClient } from '@tanstack/react-query';
import { useAuth } from '@/auth/AuthContext';
import { isPlatformOwner } from '@/utils/persona';
import { markNotificationRead } from '@/api/services/notifications.service';
import { queryKeys } from '@/lib/query-client';
import { isNativePushSupported } from '@/services/push/expo-go';
import {
  getInitialNotificationData,
  syncPushRegistration,
  subscribeToNotificationEvents,
} from '@/services/push/push-manager';
import { parsePushPayload, resolvePushRoute } from '@/services/push/deep-links';

export function PushNotificationHandler() {
  const { isAuthenticated, isBootstrapping, user } = useAuth();
  const router = useRouter();
  const queryClient = useQueryClient();
  const handledInitial = useRef(false);

  useEffect(() => {
    if (isBootstrapping || !isAuthenticated || !isNativePushSupported()) {
      return;
    }

    void syncPushRegistration();
  }, [isAuthenticated, isBootstrapping]);

  useEffect(() => {
    if (isBootstrapping || !isAuthenticated || !isNativePushSupported() || handledInitial.current) {
      return;
    }

    handledInitial.current = true;

    void (async () => {
      const data = await getInitialNotificationData();
      if (data) {
        await handleNotificationNavigation(data, router, queryClient, user);
      }
    })();
  }, [isAuthenticated, isBootstrapping, router, queryClient, user]);

  useEffect(() => {
    if (!isAuthenticated || !isNativePushSupported()) {
      return;
    }

    let unsubscribe: (() => void) | null = null;
    let active = true;

    void subscribeToNotificationEvents(
      () => {
        void queryClient.invalidateQueries({ queryKey: ['notifications'] });
        void queryClient.invalidateQueries({ queryKey: ['platform', 'notifications'] });
        void queryClient.invalidateQueries({ queryKey: ['dashboard'] });
        void queryClient.invalidateQueries({ queryKey: queryKeys.platformDashboard });
      },
      (data) => {
        void handleNotificationNavigation(data, router, queryClient, user);
      },
    ).then((cleanup) => {
      if (!active) {
        cleanup?.();
        return;
      }
      unsubscribe = cleanup;
    });

    return () => {
      active = false;
      unsubscribe?.();
    };
  }, [isAuthenticated, queryClient, router, user]);

  return null;
}

async function handleNotificationNavigation(
  data: Record<string, unknown> | undefined,
  router: ReturnType<typeof useRouter>,
  queryClient: ReturnType<typeof useQueryClient>,
  user: ReturnType<typeof useAuth>['user'],
): Promise<void> {
  const payload = parsePushPayload(data);

  if (payload.notification_id) {
    try {
      await markNotificationRead(Number(payload.notification_id));
      void queryClient.invalidateQueries({ queryKey: ['notifications'] });
    } catch {
      // Non-blocking.
    }
  }

  router.push(resolvePushRoute(payload, isPlatformOwner(user) ? 'platform' : 'clinic'));
}
