# واجهة API لتطبيق الموبايل (v1)

القاعدة: `{APP_URL}/api/v1`

## المصادقة

1. `POST /auth/login` — JSON: `email`, `password`, اختياري `device_name`, `two_factor_code`
2. الرد: `data.token` — استخدمه في كل طلب: `Authorization: Bearer {token}`
3. `GET /auth/me` — المستخدم الحالي والعيادة
4. `POST /auth/logout` — إبطال التوكن الحالي

**ملاحظات:**
- حساب `super_admin` مرفوض (403)
- يجب ربط المستخدم بعيادة (`clinic_id`)
- إن كان 2FA مفعّلاً على العيادة: أول محاولة بدون رمز ترجع 422 مع `meta.two_factor_required: true`

## معلومات التطبيق (عام)

`GET /meta` — بدون توكن

```json
{
  "data": {
    "api_version": "1.0.0",
    "min_app_version": "1.0.0",
    "app_name": "Clinic System",
    "locales": ["ar", "en"],
    "auth": { "type": "bearer", "header": "Authorization" }
  }
}
```

## اللغة

أرسل `Accept-Language: ar` أو `en` لترجمة رسائل الأخطاء.

## شكل الرد

```json
{
  "data": { ... },
  "meta": { "current_page": 1, "total": 10 }
}
```

## نقاط النهاية المحمية

تتطلب: `Authorization` + اشتراك العيادة نشط.

| Method | Path | الوصف |
|--------|------|--------|
| GET | `/dashboard` | لوحة اليوم: إحصائيات + مواعيد اليوم + طابور الزيارات |
| GET | `/doctors?q=` | قائمة الأطباء النشطين (لحجز الموعد) |
| GET | `/appointments?date=&per_page=` | مواعيد (اليوم افتراضياً) |
| POST | `/appointments` | إنشاء موعد |
| GET | `/appointments/{id}` | تفاصيل موعد |
| PUT/PATCH | `/appointments/{id}` | تعديل موعد |
| GET | `/visits?date=&status=&per_page=` | زيارات (اليوم افتراضياً) |
| GET | `/visits/{id}` | تفاصيل زيارة |
| PATCH | `/visits/{id}/status` | تغيير الحالة فقط |
| GET | `/patients?q=&per_page=` | مرضى |
| GET | `/patients/{id}` | ملف مريض |
| GET | `/notifications` | إشعارات داخل التطبيق |
| POST | `/notifications/{id}/read` | تعليم كمقروء |
| POST | `/push/register` | تسجيل توكن FCM للجهاز |
| POST | `/push/unregister` | إلغاء تسجيل توكن (أو كل أجهزة المستخدم) |

### Push (FCM)

بعد تسجيل الدخول، سجّل توكن Firebase من التطبيق:

```json
POST /push/register
Authorization: Bearer {token}
{
  "token": "fcm-device-token-from-firebase",
  "platform": "android",
  "device_name": "Samsung S24",
  "app_version": "1.0.0"
}
```

عند تسجيل الخروج (اختياري):

```json
POST /auth/logout
{ "fcm_token": "same-token" }
```

أو `POST /push/unregister` مع `{ "token": "…" }`.

عند إنشاء إشعار داخل النظام (`app_notifications`) يُرسل Push تلقائياً للأجهزة المسجّلة (إذا `PUSH_ENABLED=true`).

**إعداد الخادم:** انسخ `storage/app/firebase-credentials.json.example` إلى `firebase-credentials.json` من Firebase Console.

### إنشاء موعد (مثال)

```json
POST /appointments
{
  "patient_id": 1,
  "doctor_id": 2,
  "appointment_date": "2026-05-29",
  "start_time": "10:00",
  "end_time": "10:30",
  "status": "scheduled",
  "reason": "متابعة",
  "notes": null
}
```

حالات الموعد: `scheduled`, `confirmed`, `checked_in`, `in_progress`, `completed`, `cancelled`, `no_show`

### تغيير حالة زيارة

```json
PATCH /visits/5/status
{ "status": "in_progress" }
```

الحالات: `waiting`, `in_progress`, `completed`, `cancelled`

إن كانت العيادة تفرض فاتورة لإكمال الزيارة، إكمال `completed` بدون فاتورة يرجع 422.

## Postman

استورد الملف: `docs/postman/clinic-api-v1.json`

عيّن المتغيرات:
- `base_url` = `http://127.0.0.1:8000/api/v1`
- `token` = من استجابة login

## متغيرات البيئة

```env
SANCTUM_MOBILE_TOKEN_DAYS=30
MOBILE_API_VERSION=1.0.0
MOBILE_MIN_APP_VERSION=1.0.0
PUSH_ENABLED=true
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_CREDENTIALS=storage/app/firebase-credentials.json
```

## لاحقاً (مرحلة التطبيق)

- إنشاء زيارة من الموبايل
- فواتير ومدفوعات
- دردشة داخلية
