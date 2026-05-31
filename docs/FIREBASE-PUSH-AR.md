# إعداد Firebase + Push (قبل رفع السيرفر)

Push يحتاج **ملفين** من **نفس مشروع Firebase**:

| الملف | أين | الغرض |
|-------|-----|--------|
| `google-services.json` | `mobile/` | التطبيق يسجّل الجهاز عند FCM |
| `firebase-credentials.json` | `storage/app/` | Laravel يرسل الإشعارات |

---

## الخطوة 1 — إنشاء مشروع Firebase

1. افتح [Firebase Console](https://console.firebase.google.com/)
2. **Add project** → اسم مثل `clinic-system`
3. Google Analytics: اختياري (يمكن تعطيله)

---

## الخطوة 2 — إضافة تطبيق Android

1. Firebase → **Add app** → Android
2. **Package name:** `com.clinic.system` ← **بالضبط** (نفس `mobile/app.json`)
3. Nickname: `Clinic System`
4. SHA-1: **اتركه فارغاً الآن** (للاختبار يكفي بدونه)
5. Register app
6. **Download `google-services.json`**
7. انسخه إلى:

```
mobile/google-services.json
```

---

## الخطوة 3 — Service Account للباكند

1. Firebase → ⚙️ **Project settings** → **Service accounts**
2. **Generate new private key** → Download JSON
3. انسخه إلى:

```
storage/app/firebase-credentials.json
```

4. في `.env` (محلي + سيرفر):

```env
PUSH_ENABLED=true
FIREBASE_PROJECT_ID=your-project-id-from-firebase
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
PUSH_QUEUE=default
```

> `FIREBASE_PROJECT_ID` = قيمة `project_id` داخل الملف JSON (أو من Firebase Console).

---

## الخطوة 4 — تفعيل Cloud Messaging API

1. [Google Cloud Console](https://console.cloud.google.com/) → نفس المشروع
2. **APIs & Services** → **Library**
3. ابحث عن **Firebase Cloud Messaging API**
4. تأكد أنها **Enabled**

---

## الخطوة 5 — التحقق محلياً

```bash
# من جذر المشروع
bash scripts/verify-firebase-setup.sh

# أو يدوياً
php artisan push:verify
```

---

## الخطوة 6 — Queue worker (إجباري للـ Push)

Push يُرسل عبر Queue. يجب أن يكون worker شغّال:

**محلياً:**
```bash
php artisan queue:work
```

**على السيرفر (Docker):** يشتغل تلقائياً مع `docker compose` (خدمة `queue`).

---

## الخطوة 7 — بناء APK (بعد google-services.json)

```bash
cd mobile
npm run build:apk:cloud
```

> Push **لا يعمل** في Expo Go — لازم APK.

---

## الخطوة 8 — اختبار Push من التطبيق

1. ثبّت APK على جوال حقيقي
2. سجّل دخول بحساب عيادة
3. **الإعدادات** → فعّل **الإشعارات**
4. أنشئ إشعاراً من النظام (موعد، تنبيه، إلخ)
5. يجب أن يصل Push + يظهر داخل التطبيق

**إرسال تجريبي من السيرفر** (بعد تسجيل التطبيق):

```bash
php artisan push:verify --token=FCM_TOKEN_FROM_DEVICE
```

---

## رفع الملفات للسيرفر

الملفات **سرية** — لا ترفعها لـ GitHub:

```bash
# من جهازك → السيرفر
scp storage/app/firebase-credentials.json root@31.97.61.205:/var/www/clinic-system-main/storage/app/
```

`google-services.json` يبقى على جهازك داخل `mobile/` لبناء APK فقط.

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| التطبيق يقول "الخادم غير مهيّأ" | `PUSH_ENABLED=true` + credentials على Laravel |
| "يتطلب APK" | لا تستخدم Expo Go |
| Push لا يصل | `queue:work` شغّال + FCM API مفعّل |
| OAuth failed | تحقق من `firebase-credentials.json` |
| Invalid token | أعد تسجيل الدخول + فعّل الإشعارات من الإعدادات |

---

## iOS (لاحقاً)

يحتاج Apple Developer + APNs key في EAS credentials. Android أولاً كافي للبداية.
