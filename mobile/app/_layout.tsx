import { useEffect } from 'react';
import { Stack, useRouter, useSegments, type Href } from 'expo-router';
import { StatusBar } from 'expo-status-bar';
import '@/config/rtl';
import { AppProviders } from '@/providers/AppProviders';
import { useAuth } from '@/auth/AuthContext';
import { LoadingView } from '@/components/layout/Screen';
import { useAppTheme } from '@/providers/ThemeProvider';
import { isPlatformOwner } from '@/utils/persona';

function RootNavigator() {
  const { user, isAuthenticated, isBootstrapping } = useAuth();
  const { theme, isDark } = useAppTheme();
  const segments = useSegments();
  const router = useRouter();
  const platformUser = isPlatformOwner(user);

  useEffect(() => {
    if (isBootstrapping) {
      return;
    }

    const inAuthGroup = segments[0] === '(auth)';
    const inPlatformGroup = segments[0] === '(platform)';
    const inClinicGroup = segments[0] === '(app)';

    if (!isAuthenticated && !inAuthGroup) {
      router.replace('/(auth)/login');
      return;
    }

    if (isAuthenticated && inAuthGroup) {
      router.replace(platformUser ? ('/(platform)' as Href) : '/(app)');
      return;
    }

    if (isAuthenticated && platformUser && inClinicGroup) {
      router.replace('/(platform)' as Href);
      return;
    }

    if (isAuthenticated && !platformUser && inPlatformGroup) {
      router.replace('/(app)');
    }
  }, [isAuthenticated, isBootstrapping, segments, router, platformUser]);

  if (isBootstrapping) {
    return <LoadingView label="جاري تهيئة التطبيق..." />;
  }

  return (
    <>
      <StatusBar style={isDark ? 'light' : 'dark'} />
      <Stack
        screenOptions={{
          headerShown: false,
          contentStyle: { backgroundColor: theme.colors.background },
        }}
      >
        <Stack.Screen name="(auth)" />
        <Stack.Screen name="(app)" />
        <Stack.Screen name="(platform)" />
      </Stack>
    </>
  );
}

export default function RootLayout() {
  return (
    <AppProviders>
      <RootNavigator />
    </AppProviders>
  );
}
