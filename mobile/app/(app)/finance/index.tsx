import { useState } from 'react';
import { ScrollView } from 'react-native';
import { useLocalSearchParams, useRouter } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { fetchInvoices } from '@/api/services/invoices.service';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ListScreenHeader } from '@/components/layout/Panel';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { ChipGroup } from '@/components/ui/ChipGroup';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { InvoiceCard } from '@/features/clinic/components/InvoiceCard';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { queryKeys } from '@/lib/query-client';
import { formatApiError } from '@/utils/format';
import type { InvoiceStatus } from '@/types/api';

type StatusFilter = 'open' | 'unpaid' | 'partial' | 'paid' | 'all';

const labels: Record<StatusFilter, string> = {
  open: 'مفتوحة',
  unpaid: 'غير مدفوعة',
  partial: 'جزئية',
  paid: 'مدفوعة',
  all: 'الكل',
};

export default function FinanceScreen() {
  const router = useRouter();
  const params = useLocalSearchParams<{ status?: string }>();
  const initialFilter =
    params.status === 'unpaid' || params.status === 'partial' || params.status === 'paid'
      ? params.status
      : 'open';
  const listStyle = useListContentStyle();
  const [filter, setFilter] = useState<StatusFilter>(initialFilter as StatusFilter);

  const apiStatus: InvoiceStatus | undefined =
    filter === 'all' || filter === 'open' ? undefined : filter;

  const query = useQuery({
    queryKey: queryKeys.invoices({ status: apiStatus, open: filter === 'open' ? 1 : undefined }),
    queryFn: async () => {
      if (filter === 'open') {
        const [unpaid, partial] = await Promise.all([
          fetchInvoices({ status: 'unpaid', per_page: 30 }),
          fetchInvoices({ status: 'partial', per_page: 30 }),
        ]);
        return {
          items: [...unpaid.items, ...partial.items],
          meta: { ...unpaid.meta, total: unpaid.meta.total + partial.meta.total },
        };
      }
      return fetchInvoices({ status: apiStatus, per_page: 30 });
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

  return (
    <ScreenContainer>
      <ListScreenHeader title="الفواتير" subtitle={`${query.data?.meta.total ?? items.length} فاتورة`} />
      <ChipGroup
        options={['open', 'unpaid', 'partial', 'paid', 'all']}
        value={filter}
        onChange={setFilter}
        labels={labels}
      />
      {items.length === 0 ? (
        <EmptyState title="لا توجد فواتير" subtitle="جرّب تغيير الفلتر" />
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
              <InvoiceCard
                invoice={item}
                onPress={() =>
                  router.push({ pathname: '/(app)/finance/[id]', params: { id: String(item.id) } })
                }
              />
            </AnimatedListItem>
          ))}
        </ScrollView>
      )}
    </ScreenContainer>
  );
}
