import { ScrollView } from 'react-native';
import { useRouter } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { fetchAppointments } from '@/api/services/appointments.service';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ListScreenHeader } from '@/components/layout/Panel';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { AppointmentCard } from '@/features/appointments/components/AppointmentCard';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { queryKeys } from '@/lib/query-client';
import { formatApiError } from '@/utils/format';

export default function AppointmentsScreen() {
  const router = useRouter();
  const listStyle = useListContentStyle();
  const query = useQuery({
    queryKey: queryKeys.appointments(),
    queryFn: () => fetchAppointments(),
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

  return (
    <ScreenContainer>
      <ListScreenHeader
        title="مواعيد اليوم"
        subtitle={`${query.data?.meta.total ?? items.length} موعد`}
        actionLabel="موعد جديد"
        onAction={() => router.push('/(app)/appointments/create')}
      />

      {items.length === 0 ? (
        <EmptyState
          title="لا توجد مواعيد"
          subtitle="أنشئ موعداً جديداً للبدء"
          actionLabel="موعد جديد"
          onAction={() => router.push('/(app)/appointments/create')}
        />
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
              <AppointmentCard
                appointment={item}
                onPress={() => router.push(`/(app)/appointments/${item.id}`)}
                onPatientPress={(patientId) =>
                  router.push({ pathname: '/(app)/patients/[id]', params: { id: String(patientId) } })
                }
              />
            </AnimatedListItem>
          ))}
        </ScrollView>
      )}
    </ScreenContainer>
  );
}
