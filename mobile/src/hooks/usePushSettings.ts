import { useCallback, useEffect, useState } from 'react';
import { AppState } from 'react-native';
import {
  disablePushNotifications,
  enablePushNotifications,
  getPushStatus,
  type PushStatusSnapshot,
} from '@/services/push/push-manager';

export function usePushSettings() {
  const [snapshot, setSnapshot] = useState<PushStatusSnapshot | null>(null);
  const [busy, setBusy] = useState(false);

  const refresh = useCallback(async () => {
    const next = await getPushStatus();
    setSnapshot(next);
    return next;
  }, []);

  useEffect(() => {
    void refresh();
  }, [refresh]);

  useEffect(() => {
    const sub = AppState.addEventListener('change', (state) => {
      if (state === 'active') {
        void refresh();
      }
    });
    return () => sub.remove();
  }, [refresh]);

  const toggle = useCallback(
    async (next: boolean) => {
      setBusy(true);
      try {
        if (next) {
          await enablePushNotifications();
        } else {
          await disablePushNotifications();
        }
        await refresh();
        return true;
      } catch {
        await refresh();
        return false;
      } finally {
        setBusy(false);
      }
    },
    [refresh],
  );

  const canToggle =
    snapshot != null &&
    snapshot.status !== 'unsupported_expo_go' &&
    snapshot.status !== 'unsupported_simulator' &&
    snapshot.serverEnabled;

  return {
    snapshot,
    busy,
    canToggle,
    refresh,
    toggle,
  };
}
