import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet, Text } from 'react-native';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchVisits, updateVisitStatus } from '@/api/services/visits.service';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ListScreenHeader } from '@/components/layout/Panel';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { VisitCard } from '@/features/visits/components/VisitCard';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError, visitStatusLabels } from '@/utils/format';
import { fireHapticSuccess } from '@/utils/haptics';
import type { VisitStatus } from '@/types/api';

export default function VisitsScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const listStyle = useListContentStyle();
  const styles = useMemo(
    () =>
      StyleSheet.create({
        actionError: {
          ...theme.typography.caption,
          color: theme.colors.danger,
          textAlign: 'center',
          paddingHorizontal: theme.spacing.lg,
          marginBottom: theme.spacing.sm,
        },
      }),
    [theme],
  );
  const [busyId, setBusyId] = useState<number | null>(null);
  const [actionError, setActionError] = useState<string | null>(null);

  const query = useQuery({
    queryKey: queryKeys.visits(),
    queryFn: () => fetchVisits(),
  });

  const mutation = useMutation({
    mutationFn: ({ id, status }: { id: number; status: VisitStatus }) =>
      updateVisitStatus(id, status),
    onSuccess: async (_data, variables) => {
      setActionError(null);
      toast.showSuccess(`تم تحديث حالة الزيارة إلى ${visitStatusLabels[variables.status]}`);
      fireHapticSuccess();
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: queryKeys.visits() }),
        queryClient.invalidateQueries({ queryKey: queryKeys.dashboard }),
      ]);
    },
    onError: (err) => {
      setActionError(formatApiError(err));
    },
    onSettled: () => setBusyId(null),
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
        title="زيارات اليوم"
        subtitle={`${query.data?.meta.total ?? items.length} زيارة`}
      />

      {actionError ? <Text style={styles.actionError}>{actionError}</Text> : null}

      {items.length === 0 ? (
        <EmptyState title="لا توجد زيارات" subtitle="ستظهر زيارات اليوم هنا" />
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
              <VisitCard
                visit={item}
                loading={busyId === item.id && mutation.isPending}
                onPatientPress={(patientId) =>
                  router.push({ pathname: '/(app)/patients/[id]', params: { id: String(patientId) } })
                }
                onOpenDetail={() =>
                  router.push({ pathname: '/(app)/visits/[id]', params: { id: String(item.id) } })
                }
                onStatusChange={(status) => {
                  setBusyId(item.id);
                  mutation.mutate({ id: item.id, status });
                }}
              />
            </AnimatedListItem>
          ))}
        </ScrollView>
      )}
    </ScreenContainer>
  );
}
