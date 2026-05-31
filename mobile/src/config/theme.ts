import { Platform, type TextStyle, type ViewStyle } from 'react-native';

export type ThemeMode = 'light' | 'dark';

const fonts = {
  regular: 'Cairo_400Regular',
  medium: 'Cairo_500Medium',
  semibold: 'Cairo_600SemiBold',
  bold: 'Cairo_700Bold',
  extrabold: 'Cairo_800ExtraBold',
} as const;

const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 24,
  xxl: 32,
  xxxl: 40,
} as const;

const radius = {
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  pill: 999,
} as const;

const typography = {
  display: {
    fontSize: 28,
    fontWeight: '800' as const,
    fontFamily: fonts.extrabold,
    lineHeight: 36,
  },
  title: {
    fontSize: 22,
    fontWeight: '700' as const,
    fontFamily: fonts.bold,
    lineHeight: 30,
  },
  subtitle: {
    fontSize: 16,
    fontWeight: '600' as const,
    fontFamily: fonts.semibold,
    lineHeight: 24,
  },
  body: {
    fontSize: 15,
    fontWeight: '400' as const,
    fontFamily: fonts.regular,
    lineHeight: 22,
  },
  caption: {
    fontSize: 13,
    fontWeight: '400' as const,
    fontFamily: fonts.regular,
    lineHeight: 18,
  },
  label: {
    fontSize: 12,
    fontWeight: '600' as const,
    fontFamily: fonts.semibold,
    lineHeight: 16,
  },
  stat: {
    fontSize: 30,
    fontWeight: '800' as const,
    fontFamily: fonts.extrabold,
    lineHeight: 36,
  },
};

const lightColors = {
  brand: '#0F4C81',
  brandDark: '#0c3d66',
  brandLight: '#93C5FD',
  brandSecondary: '#1F7A8C',
  background: '#F8F9FA',
  surface: '#FFFFFF',
  surfaceMuted: '#F1F5F9',
  border: '#E5E7EB',
  borderStrong: '#CBD5E1',
  text: '#1F2937',
  textMuted: '#64748B',
  textSubtle: '#94A3B8',
  textInverse: '#FFFFFF',
  success: '#059669',
  successSoft: '#D1FAE5',
  warning: '#D97706',
  warningSoft: '#FEF3C7',
  danger: '#DC2626',
  dangerSoft: '#FEE2E2',
  info: '#2563EB',
  infoSoft: '#DBEAFE',
  tabBar: '#FFFFFF',
  overlay: 'rgba(15, 76, 129, 0.08)',
  skeleton: '#CBD5E1',
  splash: '#0F4C81',
};

const darkColors = {
  brand: '#3B82F6',
  brandDark: '#2563EB',
  brandLight: '#93C5FD',
  brandSecondary: '#1F7A8C',
  background: '#0F172A',
  surface: '#1F2937',
  surfaceMuted: '#111827',
  border: '#374151',
  borderStrong: '#4B5563',
  text: '#F3F4F6',
  textMuted: '#9CA3AF',
  textSubtle: '#6B7280',
  textInverse: '#FFFFFF',
  success: '#34D399',
  successSoft: 'rgba(16, 185, 129, 0.15)',
  warning: '#FBBF24',
  warningSoft: 'rgba(245, 158, 11, 0.15)',
  danger: '#F87171',
  dangerSoft: 'rgba(239, 68, 68, 0.15)',
  info: '#60A5FA',
  infoSoft: 'rgba(59, 130, 246, 0.15)',
  tabBar: '#111827',
  overlay: 'rgba(59, 130, 246, 0.12)',
  skeleton: '#374151',
  splash: '#0F172A',
};

function createShadows(isDark: boolean) {
  const shadowColor = isDark ? '#000000' : '#0F172A';
  return {
    sm: Platform.select<ViewStyle>({
      ios: {
        shadowColor,
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: isDark ? 0.35 : 0.06,
        shadowRadius: 3,
      },
      android: { elevation: isDark ? 3 : 2 },
      default: {},
    }),
    md: Platform.select<ViewStyle>({
      ios: {
        shadowColor,
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: isDark ? 0.4 : 0.08,
        shadowRadius: 12,
      },
      android: { elevation: isDark ? 6 : 4 },
      default: {},
    }),
    lg: Platform.select<ViewStyle>({
      ios: {
        shadowColor: isDark ? '#3B82F6' : '#0F4C81',
        shadowOffset: { width: 0, height: 8 },
        shadowOpacity: isDark ? 0.25 : 0.12,
        shadowRadius: 20,
      },
      android: { elevation: isDark ? 10 : 8 },
      default: {},
    }),
  };
}

export function createTheme(mode: ThemeMode = 'light') {
  const isDark = mode === 'dark';
  return {
    mode,
    isDark,
    colors: isDark ? darkColors : lightColors,
    fonts,
    spacing,
    radius,
    typography,
    shadows: createShadows(isDark),
  };
}

export type AppTheme = ReturnType<typeof createTheme>;

/** Default light theme for legacy imports. Prefer `useAppTheme()`. */
export const theme = createTheme('light');

export function withAlpha(hex: string, alpha: number): string {
  const normalized = hex.replace('#', '');
  const value =
    normalized.length === 3
      ? normalized
          .split('')
          .map((c) => c + c)
          .join('')
      : normalized.slice(0, 6);
  const r = parseInt(value.slice(0, 2), 16);
  const g = parseInt(value.slice(2, 4), 16);
  const b = parseInt(value.slice(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

export function textStyle(style: TextStyle): TextStyle {
  return style;
}

export const THEME_STORAGE_KEY = 'clinic-theme';
