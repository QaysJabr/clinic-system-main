import { ScrollView } from 'react-native';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchNotifications, markNotificationRead } from '@/api/services/notifications.service';
import { ListScreenHeader } from '@/components/layout/Panel';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { NotificationCard } from '@/features/notifications/components/NotificationCard';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { queryKeys } from '@/lib/query-client';
import { formatApiError } from '@/utils/format';

export default function NotificationsScreen() {
  const queryClient = useQueryClient();
  const listStyle = useListContentStyle();

  const query = useQuery({
    queryKey: queryKeys.notifications(),
    queryFn: () => fetchNotifications(),
  });

  const readMutation = useMutation({
    mutationFn: markNotificationRead,
    onSuccess: async () => {
      await queryClient.invalidateQueries({ queryKey: ['notifications'] });
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
        <ErrorView
          message={formatApiError(query.error)}
          onRetry={() => {
            void query.refetch();
          }}
        />
      </ScreenContainer>
    );
  }

  const items = query.data?.items ?? [];
  const unread = query.data?.meta.unread_count ?? 0;

  return (
    <ScreenContainer>
      <ListScreenHeader
        title="الإشعارات"
        subtitle={unread > 0 ? `${unread} غير مقروء` : 'الكل مقروء'}
      />

      {items.length === 0 ? (
        <EmptyState title="لا توجد إشعارات" subtitle="ستصلك التحديثات هنا" />
      ) : (
        <ScrollView
          contentContainerStyle={listStyle}
          refreshControl={
            <RefreshControlThemed
              refreshing={query.isRefetching}
              onRefresh={() => {
                void query.refetch();
              }}
            />
          }
        >
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
