import { useMemo, useState } from 'react';
import { ScrollView, StyleSheet, Text, View } from 'react-native';
import { Button, Input } from '@/components/ui/primitives';
import { FormSection } from '@/components/layout/FormSection';
import { useAppTheme } from '@/providers/ThemeProvider';
import type { PlatformPlan, PlatformPlanPayload } from '@/types/api';

function slugify(value: string): string {
  return value
    .trim()
    .toLowerCase()
    .replace(/[\s_]+/g, '-')
    .replace(/[^a-z0-9-]/g, '')
    .replace(/-+/g, '-');
}

function featuresToText(features: string[] | undefined): string {
  return (features ?? []).join('\n');
}

function textToFeatures(text: string): string[] {
  return text
    .split('\n')
    .map((line) => line.trim())
    .filter(Boolean);
}

export function PlanForm({
  plan,
  submitLabel,
  loading,
  onSubmit,
}: {
  plan?: PlatformPlan;
  submitLabel: string;
  loading?: boolean;
  onSubmit: (payload: PlatformPlanPayload) => Promise<void>;
}) {
  const { theme } = useAppTheme();
  const styles = useMemo(() => createStyles(theme), [theme]);
  const [name, setName] = useState(plan?.name ?? '');
  const [slug, setSlug] = useState(plan?.slug ?? '');
  const [slugTouched, setSlugTouched] = useState(Boolean(plan?.slug));
  const [priceMonthly, setPriceMonthly] = useState(plan?.price_monthly ?? '0');
  const [priceYearly, setPriceYearly] = useState(plan?.price_yearly ?? '0');
  const [maxPatients, setMaxPatients] = useState(plan?.max_patients?.toString() ?? '');
  const [maxUsers, setMaxUsers] = useState(plan?.max_users?.toString() ?? '');
  const [trialDays, setTrialDays] = useState(plan?.trial_days?.toString() ?? '0');
  const [sortOrder, setSortOrder] = useState(plan?.sort_order?.toString() ?? '0');
  const [featuresText, setFeaturesText] = useState(featuresToText(plan?.features));
  const [stripePriceId, setStripePriceId] = useState(plan?.stripe_price_id ?? '');
  const [stripePriceYearlyId, setStripePriceYearlyId] = useState(plan?.stripe_price_yearly_id ?? '');
  const [isActive, setIsActive] = useState(plan?.is_active ?? true);

  function handleNameChange(value: string) {
    setName(value);
    if (!slugTouched) {
      setSlug(slugify(value));
    }
  }

  async function handleSubmit() {
    await onSubmit({
      name: name.trim(),
      slug: slug.trim(),
      price_monthly: Number(priceMonthly),
      price_yearly: Number(priceYearly),
      max_patients: maxPatients.trim() ? Number(maxPatients) : null,
      max_users: maxUsers.trim() ? Number(maxUsers) : null,
      trial_days: Number(trialDays || 0),
      sort_order: Number(sortOrder || 0),
      is_active: isActive,
      features: textToFeatures(featuresText),
      stripe_price_id: stripePriceId.trim() || null,
      stripe_price_yearly_id: stripePriceYearlyId.trim() || null,
    });
  }

  return (
    <ScrollView contentContainerStyle={styles.content} keyboardShouldPersistTaps="handled">
      <FormSection title="معلومات الباقة" subtitle="الاسم والمعرّف الظاهر للعيادات">
        <Input label="اسم الباقة" value={name} onChangeText={handleNameChange} placeholder="Professional" />
        <Input
          label="Slug"
          value={slug}
          onChangeText={(value) => {
            setSlugTouched(true);
            setSlug(slugify(value));
          }}
          autoCapitalize="none"
          placeholder="professional"
        />
      </FormSection>

      <FormSection title="الأسعار">
        <Input
          label="السعر الشهري"
          value={priceMonthly}
          onChangeText={setPriceMonthly}
          keyboardType="decimal-pad"
        />
        <Input
          label="السعر السنوي"
          value={priceYearly}
          onChangeText={setPriceYearly}
          keyboardType="decimal-pad"
        />
      </FormSection>

      <FormSection title="الحدود">
        <Input
          label="أقصى عدد مرضى"
          value={maxPatients}
          onChangeText={setMaxPatients}
          keyboardType="number-pad"
          placeholder="غير محدود"
        />
        <Input
          label="أقصى عدد مستخدمين"
          value={maxUsers}
          onChangeText={setMaxUsers}
          keyboardType="number-pad"
          placeholder="غير محدود"
        />
        <Input label="أيام تجريبية" value={trialDays} onChangeText={setTrialDays} keyboardType="number-pad" />
        <Input label="ترتيب العرض" value={sortOrder} onChangeText={setSortOrder} keyboardType="number-pad" />
      </FormSection>

      <FormSection title="الميزات" subtitle="سطر واحد لكل ميزة">
        <Input
          label="قائمة الميزات"
          value={featuresText}
          onChangeText={setFeaturesText}
          multiline
          numberOfLines={6}
          placeholder={'مواعيد غير محدودة\nتقارير متقدمة\nدعم أولوية'}
          style={styles.featuresInput}
        />
      </FormSection>

      <FormSection title="Stripe" subtitle="معرّفات الأسعار في Stripe (اختياري)">
        <Input
          label="Stripe Price ID — شهري"
          value={stripePriceId}
          onChangeText={setStripePriceId}
          autoCapitalize="none"
          placeholder="price_..."
        />
        <Input
          label="Stripe Price ID — سنوي"
          value={stripePriceYearlyId}
          onChangeText={setStripePriceYearlyId}
          autoCapitalize="none"
          placeholder="price_..."
        />
      </FormSection>

      <FormSection title="الحالة">
        <View style={styles.toggleRow}>
          <Button
            label={isActive ? 'نشطة' : 'معطّلة'}
            variant={isActive ? 'primary' : 'secondary'}
            compact
            onPress={() => setIsActive((value) => !value)}
          />
          <Text style={styles.toggleHint}>اضغط للتبديل بين نشطة / معطّلة</Text>
        </View>
      </FormSection>

      <Button label={submitLabel} onPress={handleSubmit} loading={loading} />
    </ScrollView>
  );
}

function createStyles(theme: ReturnType<typeof useAppTheme>['theme']) {
  return StyleSheet.create({
    content: {
      padding: theme.spacing.lg,
      gap: theme.spacing.lg,
      paddingBottom: theme.spacing.xxl,
    },
    featuresInput: {
      minHeight: 120,
      textAlignVertical: 'top',
    },
    toggleRow: {
      flexDirection: 'row-reverse',
      alignItems: 'center',
      justifyContent: 'space-between',
      gap: theme.spacing.md,
    },
    toggleHint: {
      ...theme.typography.caption,
      color: theme.colors.textMuted,
      flex: 1,
      textAlign: 'right',
    },
  });
}
