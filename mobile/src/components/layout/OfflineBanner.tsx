import { useEffect, useMemo, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';
import NetInfo from '@react-native-community/netinfo';
import { AppIcon } from '@/components/ui/AppIcon';
import { useAppTheme } from '@/providers/ThemeProvider';

function isOffline(state: { isConnected: boolean | null; isInternetReachable: boolean | null }): boolean {
  if (state.isConnected === false) {
    return true;
  }
  return state.isInternetReachable === false;
}

export function OfflineBanner() {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [offline, setOffline] = useState(false);

  useEffect(() => {
    void NetInfo.fetch().then((state) => {
      setOffline(isOffline(state));
    });

    const unsubscribe = NetInfo.addEventListener((state) => {
      setOffline(isOffline(state));
    });

    return unsubscribe;
  }, []);

  if (!offline) {
    return null;
  }

  return (
    <View style={styles.banner}>
      <AppIcon name="wifi-off" size={16} color={theme.colors.textInverse} />
      <Text style={styles.text}>أنت غير متصل — يتم عرض البيانات المحفوظة</Text>
    </View>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    banner: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'center',
      gap: theme.spacing.sm,
      backgroundColor: theme.colors.warning,
      paddingVertical: theme.spacing.sm,
      paddingHorizontal: theme.spacing.lg,
    },
    text: {
      ...theme.typography.caption,
      color: theme.colors.textInverse,
      fontFamily: theme.fonts.semibold,
    },
  });
}
