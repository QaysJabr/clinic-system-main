import { AppHero } from '@/components/ui/AppHero';

export function ClinicHero({
  userName,
  clinicName,
  personaLabel,
  onSettingsPress,
}: {
  userName?: string;
  clinicName: string;
  personaLabel: string;
  onSettingsPress: () => void;
}) {
  return (
    <AppHero
      title={`مرحباً، ${userName ?? ''}`.trim() || 'مرحباً'}
      caption={personaLabel}
      subtitle={clinicName}
      subtitleIcon="building-2"
      onSettingsPress={onSettingsPress}
    />
  );
}
