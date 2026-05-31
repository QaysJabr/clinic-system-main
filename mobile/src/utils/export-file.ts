import * as FileSystem from 'expo-file-system/legacy';
import * as Sharing from 'expo-sharing';
import { env } from '@/config/env';
import { getToken } from '@/auth/token-storage';

export type ExportQueryParams = Record<string, string | number | boolean | null | undefined>;

function buildQueryString(params?: ExportQueryParams): string {
  if (!params) {
    return '';
  }

  const search = new URLSearchParams();
  for (const [key, value] of Object.entries(params)) {
    if (value === null || value === undefined || value === '') {
      continue;
    }
    search.set(key, String(value));
  }

  const query = search.toString();
  return query ? `?${query}` : '';
}

export async function downloadAndShareCsv(
  path: string,
  filename: string,
  params?: ExportQueryParams,
): Promise<void> {
  const token = await getToken();
  if (!token) {
    throw new Error('يجب تسجيل الدخول أولاً');
  }

  const basePath = path.startsWith('/') ? path : `/${path}`;
  const url = `${env.apiBaseUrl}${basePath}${buildQueryString(params)}`;
  const target = `${FileSystem.cacheDirectory ?? ''}${filename}`;

  const result = await FileSystem.downloadAsync(url, target, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'text/csv',
      'Accept-Language': env.defaultLocale,
    },
  });

  if (result.status < 200 || result.status >= 300) {
    throw new Error('تعذر تنزيل الملف');
  }

  if (await Sharing.isAvailableAsync()) {
    await Sharing.shareAsync(result.uri, {
      mimeType: 'text/csv',
      dialogTitle: 'مشاركة التصدير',
      UTI: 'public.comma-separated-values-text',
    });
    return;
  }

  throw new Error('المشاركة غير متاحة على هذا الجهاز');
}
