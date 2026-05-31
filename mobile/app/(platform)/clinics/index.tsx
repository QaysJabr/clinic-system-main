import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet, View } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useInfiniteQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import {
  activatePlatformClinic,
  exportPlatformClinicsCsv,
  fetchPlatformClinics,
  suspendPlatformClinic,
} from '@/api/services/platform.service';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ListScreenHeader } from '@/components/layout/Panel';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { ChipGroup } from '@/components/ui/ChipGroup';
import { Button, Input } from '@/components/ui/primitives';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { PlatformClinicCard } from '@/features/platform/components/PlatformClinicCard';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';

type SubscriptionFilter = 'all' | 'active' | 'expired' | 'expiring_soon';
type ActivationFilter = 'all' | 'active' | 'suspended';

const subscriptionLabels: Record<SubscriptionFilter, string> = {
  all: 'كل الاشتراكات',
  active: 'اشتراك نشط',
  expired: 'منتهية',
  expiring_soon: 'تنتهي قريباً',
};

const activationLabels: Record<ActivationFilter, string> = {
  all: 'كل الحسابات',
  active: 'حسابات نشطة',
  suspended: 'معلّقة',
};

export default function PlatformClinicsScreen() {
  const router = useRouter();
  const params = useLocalSearchParams<{ filter?: string }>();
  const initialFilter =
    params.filter === 'expiring_soon' || params.filter === 'expired' || params.filter === 'active'
      ? params.filter
      : 'all';
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const listStyle = useListContentStyle();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [search, setSearch] = useState('');
  const [subscriptionFilter, setSubscriptionFilter] = useState<SubscriptionFilter>(initialFilter);
  const [activationFilter, setActivationFilter] = useState<ActivationFilter>('all');
  const [exporting, setExporting] = useState(false);
  const [actionClinicId, setActionClinicId] = useState<number | null>(null);

  const subscriptionStatus = subscriptionFilter === 'all' ? undefined : subscriptionFilter;
  const isActive =
    activationFilter === 'all' ? undefined : activationFilter === 'active' ? ('1' as const) : ('0' as const);

  const listFilters = {
    q: search || undefined,
    subscription_status: subscriptionStatus,
    is_active: isActive,
  };

  const query = useInfiniteQuery({
    queryKey: queryKeys.platformClinics(listFilters),
    queryFn: ({ pageParam }) =>
      fetchPlatformClinics({
        ...listFilters,
        page: pageParam,
        per_page: 20,
      }),
    initialPageParam: 1,
    getNextPageParam: (lastPage) =>
      lastPage.meta.current_page < lastPage.meta.last_page ? lastPage.meta.current_page + 1 : undefined,
  });

  const activateMutation = useMutation({
    mutationFn: activatePlatformClinic,
    onSuccess: async () => {
      toast.showSuccess('تم تفعيل العيادة');
      await queryClient.invalidateQueries({ queryKey: ['platform', 'clinics'] });
      await queryClient.invalidateQueries({ queryKey: queryKeys.platformDashboard });
    },
    onError: (error) => toast.showError(formatApiError(error)),
    onSettled: () => setActionClinicId(null),
  });

  const suspendMutation = useMutation({
    mutationFn: suspendPlatformClinic,
    onSuccess: async () => {
      toast.showSuccess('تم تعليق العيادة');
      await queryClient.invalidateQueries({ queryKey: ['platform', 'clinics'] });
      await queryClient.invalidateQueries({ queryKey: queryKeys.platformDashboard });
    },
    onError: (error) => toast.showError(formatApiError(error)),
    onSettled: () => setActionClinicId(null),
  });

  const items = query.data?.pages.flatMap((page) => page.items) ?? [];
  const total = query.data?.pages[0]?.meta.total ?? items.length;
  const hasMore = Boolean(query.hasNextPage);

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

  return (
    <ScreenContainer>
      <ListScreenHeader title="إدارة العيادات" subtitle={`${total} عيادة`} />
        <View style={styles.toolbar}>
          <Input
            label="بحث"
            value={search}
            onChangeText={setSearch}
            icon="search"
            placeholder="اسم العيادة أو البريد..."
          />
          <Button
            label="تصدير CSV"
            icon="download"
            variant="secondary"
            compact
            loading={exporting}
            onPress={async () => {
              setExporting(true);
              try {
                await exportPlatformClinicsCsv(listFilters);
                toast.showSuccess('تم تجهيز ملف العيادات');
              } catch (error) {
                toast.showError(formatApiError(error));
              } finally {
                setExporting(false);
              }
            }}
          />
        </View>

        <ChipGroup
          options={['all', 'active', 'expiring_soon', 'expired']}
          value={subscriptionFilter}
          onChange={setSubscriptionFilter}
          labels={subscriptionLabels}
        />
        <ChipGroup
          options={['all', 'active', 'suspended']}
          value={activationFilter}
          onChange={setActivationFilter}
          labels={activationLabels}
        />

        {items.length === 0 ? (
          <EmptyState title="لا توجد عيادات" subtitle="جرّب تغيير معايير البحث" />
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
            {items.map((item, index) => (
              <AnimatedListItem key={item.id} index={index}>
                <PlatformClinicCard
                  clinic={item}
                  showQuickActions
                  onPress={() =>
                    router.push({ pathname: '/(platform)/clinics/[id]', params: { id: String(item.id) } })
                  }
                  onActivate={() => {
                    setActionClinicId(item.id);
                    activateMutation.mutate(item.id);
                  }}
                  onSuspend={() => {
                    setActionClinicId(item.id);
                    suspendMutation.mutate(item.id);
                  }}
                  activateLoading={actionClinicId === item.id && activateMutation.isPending}
                  suspendLoading={actionClinicId === item.id && suspendMutation.isPending}
                />
              </AnimatedListItem>
            ))}
            {hasMore ? (
              <Button
                label="تحميل المزيد"
                variant="secondary"
                loading={query.isFetchingNextPage}
                onPress={() => void query.fetchNextPage()}
              />
            ) : null}
          </ScrollView>
        )}
    </ScreenContainer>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    toolbar: {
      paddingHorizontal: theme.spacing.lg,
      paddingBottom: theme.spacing.sm,
      gap: theme.spacing.sm,
    },
  });
}
