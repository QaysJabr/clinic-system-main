import { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useQuery } from '@tanstack/react-query';
import { searchPatients } from '@/api/services/patients.service';
import { AppIcon } from '@/components/ui/AppIcon';
import { Input } from '@/components/ui/primitives';
import { withAlpha } from '@/config/theme';
import { useAppTheme } from '@/providers/ThemeProvider';
import { queryKeys } from '@/lib/query-client';
import { fireHapticLight } from '@/utils/haptics';

export function PatientPickerModal({
  visible,
  selectedId,
  onClose,
  onSelect,
}: {
  visible: boolean;
  selectedId: number | null;
  onClose: () => void;
  onSelect: (id: number, label: string) => void;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [query, setQuery] = useState('');

  useEffect(() => {
    if (!visible) {
      setQuery('');
    }
  }, [visible]);

  const patientsQuery = useQuery({
    queryKey: queryKeys.patients(query.trim()),
    queryFn: () => searchPatients(query.trim()),
    enabled: visible && query.trim().length >= 1,
  });

  const items = patientsQuery.data?.items ?? [];

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <View style={styles.backdrop}>
        <View style={styles.sheet}>
          <View style={styles.header}>
            <Pressable onPress={onClose} style={styles.closeBtn} hitSlop={8}>
              <AppIcon name="x" size={20} color={theme.colors.textMuted} />
            </Pressable>
            <Text style={styles.title}>اختيار مريض</Text>
          </View>

          <View style={styles.body}>
            <Input
              label="بحث"
              value={query}
              onChangeText={setQuery}
              icon="search"
              placeholder="الاسم أو الهاتف"
              autoFocus
            />

            {patientsQuery.isFetching ? (
              <ActivityIndicator color={theme.colors.brand} style={styles.loader} />
            ) : null}

            <ScrollView contentContainerStyle={styles.list} keyboardShouldPersistTaps="handled">
              {query.trim().length === 0 ? (
                <Text style={styles.hint}>ابدأ الكتابة للبحث عن مريض</Text>
              ) : items.length === 0 ? (
                <Text style={styles.hint}>لا توجد نتائج</Text>
              ) : (
                items.map((patient) => {
                  const active = patient.id === selectedId;
                  return (
                    <Pressable
                      key={patient.id}
                      style={[styles.option, active && styles.optionActive]}
                      onPress={() => {
                        fireHapticLight();
                        onSelect(patient.id, patient.full_name);
                        onClose();
                      }}
                    >
                      <Text style={[styles.optionLabel, active && styles.optionLabelActive]}>
                        {patient.full_name}
                      </Text>
                      {patient.phone ? <Text style={styles.optionSub}>{patient.phone}</Text> : null}
                    </Pressable>
                  );
                })
              )}
            </ScrollView>
          </View>
        </View>
      </View>
    </Modal>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    backdrop: {
      flex: 1,
      backgroundColor: withAlpha('#000000', theme.isDark ? 0.62 : 0.45),
      justifyContent: 'flex-end',
    },
    sheet: {
      maxHeight: '82%',
      backgroundColor: theme.colors.surface,
      borderTopLeftRadius: theme.radius.xl,
      borderTopRightRadius: theme.radius.xl,
      ...theme.shadows.lg,
    },
    header: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      paddingHorizontal: theme.spacing.lg,
      paddingVertical: theme.spacing.md,
      borderBottomWidth: 1,
      borderBottomColor: theme.colors.border,
    },
    title: {
      ...theme.typography.subtitle,
      color: theme.colors.text,
    },
    closeBtn: {
      width: 36,
      height: 36,
      borderRadius: theme.radius.sm,
      backgroundColor: theme.colors.surfaceMuted,
      alignItems: 'center',
      justifyContent: 'center',
    },
    body: {
      padding: theme.spacing.lg,
      gap: theme.spacing.md,
    },
    loader: {
      marginVertical: theme.spacing.sm,
    },
    list: {
      gap: theme.spacing.sm,
      paddingBottom: theme.spacing.xxl,
    },
    hint: {
      ...theme.typography.body,
      color: theme.colors.textMuted,
      textAlign: 'center',
      paddingVertical: theme.spacing.xl,
    },
    option: {
      borderWidth: 1,
      borderColor: theme.colors.border,
      borderRadius: theme.radius.md,
      padding: theme.spacing.md,
      backgroundColor: theme.colors.surface,
    },
    optionActive: {
      borderColor: theme.colors.brand,
      backgroundColor: theme.colors.overlay,
    },
    optionLabel: {
      ...theme.typography.body,
      color: theme.colors.text,
      textAlign: 'right',
    },
    optionLabelActive: {
      color: theme.colors.brand,
      fontFamily: theme.fonts.bold,
    },
    optionSub: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      textAlign: 'right',
      marginTop: 4,
    },
  });
}
