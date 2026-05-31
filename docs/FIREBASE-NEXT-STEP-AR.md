# ⚠️ ملفان مختلفان — لا تخلط بينهما

## ✅ ملف 1 — google-services.json (جاهز عندك)

- **من:** Firebase → Add Android app → Download
- **المكان:** `mobile/google-services.json`
- **الغرض:** التطبيق يسجّل الجهاز عند FCM
- **الحالة:** ✅ موجود وصحيح (`com.clinic.system`, project: `clinic-system-56227`)

---

## ❌ ملف 2 — firebase-credentials.json (ناقص)

- **من:** Firebase → ⚙️ Project settings → **Service accounts** → **Generate new private key**
- **المكان:** `storage/app/firebase-credentials.json`
- **الغرض:** Laravel يرسل Push للجوال
- **الحالة:** ❌ غير موجود

### كيف تعرف الملف الصح؟

Service Account JSON يبدأ هكذا:

```json
{
  "type": "service_account",
  "project_id": "clinic-system-56227",
  "private_key": "-----BEGIN PRIVATE KEY-----\n...",
  "client_email": "firebase-adminsdk-...@clinic-system-56227.iam.gserviceaccount.com"
}
```

**ليس** نفس `google-services.json` (اللي فيه `project_info` و `client`).

---

## خطوات سريعة

1. [Firebase Console](https://console.firebase.google.com/) → مشروع **clinic-system-56227**
2. ⚙️ **Project settings** → تبويب **Service accounts**
3. **Generate new private key** → Download
4. احفظ الملف كـ:

```
storage/app/firebase-credentials.json
```

5. تحقق:

```bash
php artisan push:verify
```

6. build APK:

```bash
cd mobile
npm run build:apk:cloud
```

---

## ما تم إنجازه تلقائياً

- `FIREBASE_PROJECT_ID=clinic-system-56227` في `.env`
- `mobile/google-services.json` ✅
- `mobile/app.json` مربوط بـ google-services ✅
- `PUSH_ENABLED=true` ✅
