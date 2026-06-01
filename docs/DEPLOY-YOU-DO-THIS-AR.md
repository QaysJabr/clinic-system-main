# ما يجب أن تفعله أنت (سيرفر + `.env` فقط)

الكود والاختبارات جاهزة على `main`. هذه الخطوات **لا يستطيع الوكيل تنفيذها** على جهازك أو VPS.

---

## 1) السيرفر (`31.97.61.205`)

اتصل عبر SSH ثم:

```bash
cd /var/www/clinic-system-main
git pull origin main
bash scripts/fix-all-production.sh
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

تحقق:

```bash
php artisan test --parallel=0   # اختياري على السيرفر
curl -s http://127.0.0.1/health | head
```

---

## 2) ملف `.env` على السيرفر (أضف أو عدّل)

انسخ من `.env.example` إن لزم. الأهم للإنتاج:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://31.97.61.205

# أمان Phase 1
PATIENT_PORTAL_TOKEN_TTL_DAYS=90
SCHEDULING_PUBLIC_BOOKING_RATE_LIMIT=30

# عندما تكون جاهزاً لفرض 2FA
SECURITY_2FA_ENABLED=true

# Firebase (إن لم تكن مضبوطة)
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
```

بعد أي تعديل على `.env`:

```bash
php artisan config:clear
php artisan config:cache
```

---

## 3) تطبيق الموبايل (على جهازك)

```bash
cd mobile
cp .env.example .env
# عدّل EXPO_PUBLIC_API_URL إن تغيّر عنوان السيرفر
npm install
npx expo start
```

للبناء عبر EAS، `EXPO_PUBLIC_API_URL` مضبوط في `mobile/eas.json` — غيّره إذا غيّرت IP/دومين السيرفر.

---

## 4) ما تم في الكود (لا تحتاج تعديله)

- تصلّب الحجز العام، الاشتراك، التزامن، CI
- اختبارات API للمرضى والفواتير والمدفوعات
- الموبايل يقرأ `apiBaseUrl` من `app.config.js` / `EXPO_PUBLIC_API_URL` (وليس IP ثابتاً في الكود فقط)

---

## 5) اختياري لاحقاً

- شهادة HTTPS + تحديث `APP_URL` وروابط الموبايل إلى `https://`
- تفعيل `EMAIL_VERIFICATION_ENABLED=true`
- Horizon إذا أضفت الحزمة في `composer.json`
