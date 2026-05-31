import { useEffect } from 'react';
import { GestureHandlerRootView } from 'react-native-gesture-handler';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import * as SplashScreen from 'expo-splash-screen';
import { StyleSheet, View } from 'react-native';
import { AuthProvider } from '@/auth/AuthContext';
import { AppMetaGuard } from '@/components/AppMetaGuard';
import { OfflineBanner } from '@/components/layout/OfflineBanner';
import { useAppFonts } from '@/config/fonts';
import { useAppFocusRefresh } from '@/hooks/usePushNotifications';
import { PushNotificationHandler } from '@/hooks/PushNotificationHandler';
import { ThemeProvider } from '@/providers/ThemeProvider';
import { ToastProvider } from '@/providers/ToastProvider';
import { isExpoGo } from '@/services/push/expo-go';
import { PersistQueryProvider } from '@/lib/persist-query';

SplashScreen.preventAutoHideAsync().catch(() => {
  // Splash may already be managed by Expo Go.
});

function AppLifecycle() {
  useAppFocusRefresh();
  return (
    <>
      <AppMetaGuard />
      {!isExpoGo() ? <PushNotificationHandler /> : null}
    </>
  );
}

export function AppProviders({ children }: { children: React.ReactNode }) {
  const fontsLoaded = useAppFonts();

  useEffect(() => {
    if (fontsLoaded) {
      void SplashScreen.hideAsync();
    }
  }, [fontsLoaded]);

  if (!fontsLoaded) {
    return null;
  }

  return (
    <GestureHandlerRootView style={styles.root}>
      <SafeAreaProvider>
        <ThemeProvider>
          <PersistQueryProvider>
            <ToastProvider>
              <AuthProvider>
                <View style={styles.shell}>
                  <OfflineBanner />
                  <View style={styles.appBody}>
                    <AppLifecycle />
                    {children}
                  </View>
                </View>
              </AuthProvider>
            </ToastProvider>
          </PersistQueryProvider>
        </ThemeProvider>
      </SafeAreaProvider>
    </GestureHandlerRootView>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
  },
  shell: {
    flex: 1,
  },
  appBody: {
    flex: 1,
  },
});
