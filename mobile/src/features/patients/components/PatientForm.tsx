import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet } from 'react-native';
import { FormSection } from '@/components/layout/FormSection';
import { Button, Input } from '@/components/ui/primitives';
import { ChipGroup } from '@/components/ui/ChipGroup';
import { useFormScrollPadding } from '@/components/layout/FormScreen';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { Patient, PatientPayload } from '@/types/api';

export function PatientForm({
  submitLabel,
  loading,
  onSubmit,
}: {
  submitLabel: string;
  loading?: boolean;
  onSubmit: (payload: PatientPayload) => Promise<Patient | void>;
}) {
  const { theme } = useAppTheme();
  const scrollPadding = useFormScrollPadding();
  const styles = useMemo(() => createStyles(theme, scrollPadding), [theme, scrollPadding]);
  const [fullName, setFullName] = useState('');
  const [phone, setPhone] = useState('');
  const [gender, setGender] = useState<'male' | 'female'>('male');
  const [notes, setNotes] = useState('');

  async function handleSubmit() {
    await onSubmit({
      full_name: fullName.trim(),
      phone: phone.trim() || null,
      gender,
      notes: notes.trim() || null,
    });
  }

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <FormSection title="بيانات المريض" subtitle="الحقول الأساسية — رقم الملف يُولَّد تلقائياً">
        <Input label="الاسم الكامل" value={fullName} onChangeText={setFullName} placeholder="محمد أحمد" />
        <Input label="الهاتف" value={phone} onChangeText={setPhone} keyboardType="phone-pad" placeholder="07xxxxxxxx" />
        <ChipGroup
          options={['male', 'female']}
          value={gender}
          onChange={(value) => setGender(value as 'male' | 'female')}
          labels={{ male: 'ذكر', female: 'أنثى' }}
        />
        <Input
          label="ملاحظات"
          value={notes}
          onChangeText={setNotes}
          multiline
          numberOfLines={3}
          placeholder="حساسية، ملاحظات استقبال..."
          style={styles.notesInput}
        />
      </FormSection>
      <Button label={submitLabel} icon="plus" onPress={handleSubmit} loading={loading} disabled={!fullName.trim()} />
    </ScrollView>
  );
}

function createStyles(
  theme: ReturnType<typeof useAppTheme>['theme'],
  content: ReturnType<typeof useFormScrollPadding>,
) {
  return StyleSheet.create({
    content,
    notesInput: {
      minHeight: 88,
      textAlignVertical: 'top',
    },
  });
}
