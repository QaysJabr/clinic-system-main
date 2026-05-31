import type { LucideIcon } from 'lucide-react-native';
import { useAppTheme } from '@/providers/ThemeProvider';

type IconName =
  | 'home'
  | 'calendar'
  | 'users'
  | 'bell'
  | 'chevron-left'
  | 'chevron-right'
  | 'plus'
  | 'log-out'
  | 'clock'
  | 'stethoscope'
  | 'file-text'
  | 'alert-circle'
  | 'inbox'
  | 'refresh-cw'
  | 'search'
  | 'building-2'
  | 'circle-user'
  | 'lock'
  | 'mail'
  | 'check'
  | 'x'
  | 'moon'
  | 'sun'
  | 'fingerprint'
  | 'wifi-off'
  | 'user'
  | 'phone'
  | 'download'
  | 'settings'
  | 'eye'
  | 'eye-off'
  | 'shield'
  | 'palette'
  | 'info';

import {
  AlertCircle,
  Bell,
  Building2,
  Calendar,
  ChevronLeft,
  ChevronRight,
  CircleUser,
  Clock,
  FileText,
  Home,
  Inbox,
  Lock,
  LogOut,
  Mail,
  Plus,
  RefreshCw,
  Search,
  Stethoscope,
  Users,
  Check,
  X,
  Moon,
  Sun,
  Fingerprint,
  WifiOff,
  User,
  Phone,
  Download,
  Settings,
  Eye,
  EyeOff,
  Shield,
  Palette,
  Info,
} from 'lucide-react-native';

const ICONS: Record<IconName, LucideIcon> = {
  home: Home,
  calendar: Calendar,
  users: Users,
  bell: Bell,
  'chevron-left': ChevronLeft,
  'chevron-right': ChevronRight,
  plus: Plus,
  'log-out': LogOut,
  clock: Clock,
  stethoscope: Stethoscope,
  'file-text': FileText,
  'alert-circle': AlertCircle,
  inbox: Inbox,
  'refresh-cw': RefreshCw,
  search: Search,
  'building-2': Building2,
  'circle-user': CircleUser,
  lock: Lock,
  mail: Mail,
  check: Check,
  x: X,
  moon: Moon,
  sun: Sun,
  fingerprint: Fingerprint,
  'wifi-off': WifiOff,
  user: User,
  phone: Phone,
  download: Download,
  settings: Settings,
  eye: Eye,
  'eye-off': EyeOff,
  shield: Shield,
  palette: Palette,
  info: Info,
};

export function AppIcon({
  name,
  size = 22,
  color,
  strokeWidth = 1.75,
}: {
  name: IconName;
  size?: number;
  color?: string;
  strokeWidth?: number;
}) {
  const { theme } = useAppTheme();
  const Icon = ICONS[name];
  return <Icon size={size} color={color ?? theme.colors.text} strokeWidth={strokeWidth} />;
}
