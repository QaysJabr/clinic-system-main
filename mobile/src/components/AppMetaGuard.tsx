import { useEffect } from 'react';
import { Alert } from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { fetchMeta } from '@/api/services/auth.service';
import { queryKeys } from '@/lib/query-client';
import { isAppOutdated } from '@/utils/version';

export function AppMetaGuard() {
  const metaQuery = useQuery({
    queryKey: queryKeys.meta,
    queryFn: fetchMeta,
    staleTime: 10 * 60_000,
  });

  useEffect(() => {
    const meta = metaQuery.data;
    if (!meta) {
      return;
    }

    if (isAppOutdated(meta.min_app_version)) {
      Alert.alert(
        'تحديث مطلوب',
        `إصدار التطبيق (${meta.min_app_version} أو أحدث) مطلوب للمتابعة. راجع متجر التطبيقات.`,
      );
    }
  }, [metaQuery.data]);

  return null;
}
