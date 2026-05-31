import { useMemo } from 'react';
import { StyleSheet } from 'react-native';
import { Tabs } from 'expo-router';
import { useQuery } from '@tanstack/react-query';
import { fetchPlatformNotifications } from '@/api/services/platform.service';
import { AppIcon } from '@/components/ui/AppIcon';
import { queryKeys } from '@/lib/query-client';
import { useTabBarStyle } from '@/hooks/useTabBarStyle';
import { useAppTheme } from '@/providers/ThemeProvider';

export default function PlatformLayout() {
  const { theme } = useAppTheme();
  const tabBarStyle = useTabBarStyle();
  const styles = useMemo(() => createStyles(theme), [theme]);

  const notificationsQuery = useQuery({
    queryKey: queryKeys.platformNotifications(),
    queryFn: () => fetchPlatformNotifications(1),
    staleTime: 60_000,
  });

  const unread = notificationsQuery.data?.meta.unread_count ?? 0;

  return (
    <Tabs
      screenOptions={{
        headerShown: false,
        tabBarActiveTintColor: theme.colors.brand,
        tabBarInactiveTintColor: theme.colors.textMuted,
        tabBarStyle,
        tabBarLabelStyle: styles.tabLabel,
        tabBarItemStyle: styles.tabItem,
        sceneStyle: { backgroundColor: theme.colors.background },
      }}
    >
      <Tabs.Screen
        name="index"
        options={{
          title: 'المنصّة',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name="building-2" color={String(color)} size={focused ? 24 : 22} strokeWidth={focused ? 2.25 : 1.75} />
          ),
        }}
      />
      <Tabs.Screen
        name="clinics"
        options={{
          title: 'العيادات',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name="users" color={String(color)} size={focused ? 24 : 22} strokeWidth={focused ? 2.25 : 1.75} />
          ),
        }}
      />
      <Tabs.Screen
        name="plans"
        options={{
          title: 'الباقات',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name="file-text" color={String(color)} size={focused ? 24 : 22} strokeWidth={focused ? 2.25 : 1.75} />
          ),
        }}
      />
      <Tabs.Screen
        name="notifications"
        options={{
          title: 'إشعارات',
          tabBarIcon: ({ color, focused }) => (
            <AppIcon name="bell" color={String(color)} size={focused ? 24 : 22} strokeWidth={focused ? 2.25 : 1.75} />
          ),
          tabBarBadge: unread > 0 ? unread : undefined,
          tabBarBadgeStyle: styles.badge,
        }}
      />
      <Tabs.Screen name="settings" options={{ href: null }} />
    </Tabs>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    tabLabel: {
      ...theme.typography.label,
      marginTop: 2,
    },
    tabItem: {
      paddingTop: 4,
    },
    badge: {
      backgroundColor: theme.colors.danger,
      color: theme.colors.textInverse,
      fontFamily: theme.fonts.bold,
      fontSize: 10,
    },
  });
}
