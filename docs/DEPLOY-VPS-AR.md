# نشر الباكند على VPS (سيرفرك)

> **قبل الرفع:** أكمل Firebase + Push — راجع `docs/FIREBASE-PUSH-AR.md`

## أين يرفع إيش؟

| المكوّن | أين يُرفع |
|---------|-----------|
| **Laravel (الباكند + API)** | السيرفر `/var/www/clinic-system-main` ✅ |
| **تطبيق الجوال (APK)** | **لا** على السيرفر — يُبنى على جهازك ويُثبّت على الجوال |

السيرفر `31.97.61.205` والمسار `/var/www/clinic-system-main` **صح** للباكند.

---

## تشغيل كامل (أمر واحد)

على السيرفر (SSH):

```bash
cd /var/www/clinic-system-main
git pull
bash scripts/setup-vps-production.sh
```

المتطلبات على السيرفر:
- Docker + Docker Compose
- Node.js 20+ و npm
- Git

---

## ماذا يفعل السكربت؟

1. ينشئ `.env` للإنتاج (كلمات مرور عشوائية)
2. يبني واجهة الويب (`npm run build`)
3. يشغّل: PostgreSQL + Redis + PHP + Nginx + Queue + Scheduler
4. يشغّل migrations و seed لحساب مالك المنصة
5. يفتح الموقع على: `http://31.97.61.205`

---

## بعد النشر — ربط تطبيق الجوال

في مجلد `mobile` على جهازك:

```env
EXPO_PUBLIC_API_URL=http://31.97.61.205/api/v1
```

ثم build APK:

```bash
cd mobile
npm run build:apk:cloud
```

---

## التحقق

```bash
curl http://31.97.61.205/up
curl http://31.97.61.205/api/v1/meta
```

---

## HTTPS (لاحقاً)

عندما يكون عندك دومين (مثلاً `clinic.example.com`):

1. عدّل `APP_URL=https://clinic.example.com` في `.env`
2. Certbot + Nginx على المضيف أو reverse proxy
3. حدّث `EXPO_PUBLIC_API_URL` في التطبيق إلى `https://...`

---

## Push (اختياري)

1. Firebase → `google-services.json` → `mobile/`
2. Service account → `storage/app/firebase-credentials.json`
3. `PUSH_ENABLED=true` في `.env`
4. إعادة تشغيل queue: `docker compose restart queue`
