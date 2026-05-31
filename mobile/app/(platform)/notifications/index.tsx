import { ScrollView } from 'react-native';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  fetchPlatformNotifications,
  markAllPlatformNotificationsRead,
  markPlatformNotificationRead,
} from '@/api/services/platform.service';
import { ListScreenHeader } from '@/components/layout/Panel';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { Button } from '@/components/ui/primitives';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { NotificationCard } from '@/features/notifications/components/NotificationCard';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { queryKeys } from '@/lib/query-client';
import { formatApiError } from '@/utils/format';

export default function PlatformNotificationsScreen() {
  const queryClient = useQueryClient();
  const listStyle = useListContentStyle();

  const query = useQuery({
    queryKey: queryKeys.platformNotifications(),
    queryFn: () => fetchPlatformNotifications(),
  });

  const readMutation = useMutation({
    mutationFn: markPlatformNotificationRead,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['platform', 'notifications'] });
    },
  });

  const readAllMutation = useMutation({
    mutationFn: markAllPlatformNotificationsRead,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['platform', 'notifications'] });
    },
  });

  if (query.isLoading) {
    return (
      <ScreenContainer>
        <ListScreenSkeleton />
      </ScreenContainer>
    );
  }

  if (query.isError) {
    return (
      <ScreenContainer>
        <ErrorView message={formatApiError(query.error)} onRetry={() => void query.refetch()} />
      </ScreenContainer>
    );
  }

  const items = query.data?.items ?? [];
  const unread = query.data?.meta.unread_count ?? 0;

  return (
    <ScreenContainer>
      <ListScreenHeader
        title="إشعارات المنصّة"
        subtitle={unread > 0 ? `${unread} غير مقروء` : 'الكل مقروء'}
        actionLabel={unread > 0 ? 'قراءة الكل' : undefined}
        onAction={unread > 0 ? () => readAllMutation.mutate() : undefined}
      />

      {items.length === 0 ? (
        <EmptyState title="لا توجد إشعارات" subtitle="ستصلك تنبيهات إدارة المنصّة هنا" />
      ) : (
        <ScrollView
          contentContainerStyle={listStyle}
          refreshControl={
            <RefreshControlThemed
              refreshing={query.isRefetching}
              onRefresh={() => void query.refetch()}
            />
          }
        >
          {unread > 0 ? (
            <Button
              label="تعليم الكل كمقروء"
              variant="secondary"
              icon="check"
              onPress={() => readAllMutation.mutate()}
              loading={readAllMutation.isPending}
            />
          ) : null}
          {items.map((item, index) => (
            <AnimatedListItem key={item.id} index={index}>
              <NotificationCard
                notification={item}
                onPress={() => {
                  if (!item.is_read) {
                    readMutation.mutate(item.id);
                  }
                }}
              />
            </AnimatedListItem>
          ))}
        </ScrollView>
      )}
    </ScreenContainer>
  );
}
