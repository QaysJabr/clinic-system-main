import { useMemo } from 'react';
import { Pressable, ScrollView, StyleSheet, Text, View } from 'react-native';
import { useRouter } from 'expo-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchPlatformPlans, togglePlatformPlan } from '@/api/services/platform.service';
import { EmptyState, ErrorView, ScreenContainer } from '@/components/layout/Screen';
import { ListScreenHeader } from '@/components/layout/Panel';
import { AnimatedListItem } from '@/components/ui/AnimatedListItem';
import { AppIcon } from '@/components/ui/AppIcon';
import { Badge, Button, Card } from '@/components/ui/primitives';
import { ListScreenSkeleton } from '@/components/ui/Skeleton';
import { RefreshControlThemed } from '@/components/ui/RefreshControlThemed';
import { useListContentStyle } from '@/hooks/useListContentStyle';
import { queryKeys } from '@/lib/query-client';
import { useAppTheme } from '@/providers/ThemeProvider';
import { useToast } from '@/providers/ToastProvider';
import { formatApiError } from '@/utils/format';

export default function PlatformPlansScreen() {
  const router = useRouter();
  const queryClient = useQueryClient();
  const toast = useToast();
  const { theme } = useAppTheme();
  const listStyle = useListContentStyle();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const query = useQuery({
    queryKey: queryKeys.platformPlans,
    queryFn: fetchPlatformPlans,
  });

  const toggleMutation = useMutation({
    mutationFn: togglePlatformPlan,
    onSuccess: async () => {
      toast.showSuccess('تم تحديث حالة الباقة');
      await queryClient.invalidateQueries({ queryKey: queryKeys.platformPlans });
    },
    onError: (error) => toast.showError(formatApiError(error)),
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

  const plans = query.data ?? [];

  return (
    <ScreenContainer>
      <ListScreenHeader
          title="باقات الاشتراك"
          subtitle={`${plans.length} باقة`}
          actionLabel="باقة جديدة"
          onAction={() => router.push('/(platform)/plans/create' as import('expo-router').Href)}
        />

        {plans.length === 0 ? (
          <EmptyState
            title="لا توجد باقات"
            subtitle="أنشئ أول باقة للمنصّة"
            actionLabel="باقة جديدة"
            onAction={() => router.push('/(platform)/plans/create' as import('expo-router').Href)}
          />
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
            {plans.map((plan, index) => {
              const hasStripe = Boolean(plan.stripe_price_id || plan.stripe_price_yearly_id);
              const limits = [
                plan.max_patients != null ? `${plan.max_patients} مريض` : 'مرضى غير محدود',
                plan.max_users != null ? `${plan.max_users} مستخدم` : 'مستخدمين غير محدود',
              ].join(' · ');

              return (
                <AnimatedListItem key={plan.id} index={index}>
                  <Pressable
                    onPress={() =>
                      router.push({ pathname: '/(platform)/plans/[id]', params: { id: String(plan.id) } })
                    }
                  >
                    <Card>
                      <View style={styles.header}>
                        <View style={styles.badges}>
                          <Badge
                            label={plan.is_active ? 'نشطة' : 'معطّلة'}
                            color={plan.is_active ? theme.colors.success : theme.colors.textMuted}
                          />
                          {hasStripe ? (
                            <Badge label="Stripe ✓" color={theme.colors.info} />
                          ) : (
                            <Badge label="بدون Stripe" color={theme.colors.textMuted} />
                          )}
                        </View>
                        <Text style={styles.count}>{plan.clinics_count} عيادة</Text>
                      </View>
                      <Text style={styles.name}>{plan.name}</Text>
                      <Text style={styles.slug}>{plan.slug} · ترتيب {plan.sort_order}</Text>
                      <Text style={styles.price}>{plan.display_monthly}</Text>
                      <Text style={styles.price}>{plan.display_yearly}</Text>
                      <Text style={styles.limits}>{limits}</Text>
                      {plan.features.length > 0 ? (
                        <View style={styles.featuresWrap}>
                          <AppIcon name="check" size={14} color={theme.colors.success} />
                          <Text style={styles.features} numberOfLines={2}>
                            {plan.features.join(' · ')}
                          </Text>
                        </View>
                      ) : null}
                      <View style={styles.actions}>
                        <Button
                          label={plan.is_active ? 'تعطيل' : 'تفعيل'}
                          variant={plan.is_active ? 'danger' : 'primary'}
                          compact
                          loading={toggleMutation.isPending && toggleMutation.variables === plan.id}
                          onPress={() => toggleMutation.mutate(plan.id)}
                        />
                        <Button
                          label="تعديل"
                          variant="secondary"
                          compact
                          onPress={() =>
                            router.push({ pathname: '/(platform)/plans/[id]', params: { id: String(plan.id) } })
                          }
                        />
                      </View>
                    </Card>
                  </Pressable>
                </AnimatedListItem>
              );
            })}
          </ScrollView>
        )}
    </ScreenContainer>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    header: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      alignItems: 'flex-start',
      marginBottom: theme.spacing.sm,
      gap: theme.spacing.sm,
    },
    badges: {
      flexDirection: 'row-reverse',
      flexWrap: 'wrap',
      gap: theme.spacing.xs,
      flex: 1,
    },
    count: { ...theme.typography.caption, color: theme.colors.textMuted },
    name: { ...theme.typography.subtitle, color: theme.colors.text, textAlign: 'right' },
    slug: { ...theme.typography.caption, color: theme.colors.brand, textAlign: 'right', marginTop: 2 },
    price: { ...theme.typography.body, color: theme.colors.textMuted, textAlign: 'right', marginTop: 4 },
    limits: { ...theme.typography.caption, color: theme.colors.text, textAlign: 'right', marginTop: theme.spacing.sm },
    featuresWrap: {
      flexDirection: 'row-reverse',
      alignItems: 'flex-start',
      gap: 6,
      marginTop: theme.spacing.sm,
    },
    features: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
      flex: 1,
    },
    actions: { flexDirection: 'row-reverse', gap: theme.spacing.sm, marginTop: theme.spacing.md },
  });
}
