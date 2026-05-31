import { useMemo } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';
import { AppIcon } from '@/components/ui/AppIcon';
import { Card } from '@/components/ui/primitives';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { AppNotification } from '@/types/api';

function formatRelativeTime(iso: string): string {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) {
    return iso;
  }
  return date.toLocaleString('ar', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export function NotificationCard({
  notification,
  onPress,
}: {
  notification: AppNotification;
  onPress: () => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);

  return (
    <Pressable onPress={onPress} style={({ pressed }) => [pressed && styles.pressed]}>
      <Card elevated={!notification.is_read}>
        <View style={styles.row}>
          <View style={[styles.iconWrap, !notification.is_read && styles.iconWrapUnread]}>
            <AppIcon
              name="bell"
              size={18}
              color={notification.is_read ? theme.colors.textMuted : theme.colors.brand}
            />
          </View>
          <View style={styles.body}>
            <View style={styles.header}>
              <Text style={styles.type}>{notification.type_label}</Text>
              <Text style={styles.time}>{formatRelativeTime(notification.created_at)}</Text>
            </View>
            <Text style={[styles.title, !notification.is_read && styles.titleUnread]}>
              {notification.title}
            </Text>
            <Text style={styles.message}>{notification.message}</Text>
          </View>
          {!notification.is_read ? <View style={styles.dot} /> : null}
        </View>
      </Card>
    </Pressable>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    row: {
      flexDirection: 'row-reverse',
      alignItems: 'flex-start',
      gap: theme.spacing.sm,
    },
    iconWrap: {
      width: 40,
      height: 40,
      borderRadius: theme.radius.sm,
      backgroundColor: theme.colors.surfaceMuted,
      alignItems: 'center',
      justifyContent: 'center',
    },
    iconWrapUnread: {
      backgroundColor: withAlpha(theme.colors.brand, 0.1),
    },
    dot: {
      width: 8,
      height: 8,
      borderRadius: 4,
      backgroundColor: theme.colors.brand,
      marginTop: 6,
    },
    body: {
      flex: 1,
      gap: 4,
    },
    header: {
      flexDirection: 'row-reverse',
      justifyContent: 'space-between',
      gap: theme.spacing.sm,
    },
    type: {
      ...theme.typography.label,
      color: theme.colors.brand,
    },
    time: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
    },
    title: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
    },
    titleUnread: {
      fontFamily: theme.fonts.bold,
    },
    message: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
    },
    pressed: {
      opacity: 0.92,
    },
  });
}
