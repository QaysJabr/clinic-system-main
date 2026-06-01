# مختبر الاختبار المحلي (Testing Lab)

للتشغيل **على جهازك فقط** — مو على سيرفر الإنتاج.

## المتطلبات

1. PostgreSQL محلي مع قاعدة **`clinic_test_db`**
2. `composer install` (يشمل PHPUnit)
3. في `.env` المحلي:
   - `APP_ENV=local`
   - للاختبارات PHPUnit يستخدم `phpunit.xml` → `DB_DATABASE=clinic_test_db`

> لا تشغّل `lab:smoke` إذا `APP_ENV=production` أو قاعدة البيانات اسمها `clinic` (الإنتاج).

## أمر واحد

```powershell
cd C:\Users\QAYS\Desktop\clinic-system-main
php artisan lab:smoke
```

أو مباشرة:

```powershell
php artisan test --testsuite=Lab
```

## ماذا يختبر؟

| ملف | الغرض |
|-----|--------|
| `ClinicDayFlowLabTest` | يوم عمل كامل: مريض → موعد → check-in → فاتورة → دفع |
| `TenantIsolationLabTest` | عيادة ب لا ترى مريض عيادة أ |
| `ConcurrencyLabTest` | رفض دفع يتجاوز المتبقي |
| `RoleSmokeLabTest` | طبيب / استقبال / محاسب — صفحاتهم الأساسية |

## التقارير

بعد `lab:smoke`:

- `storage/testing-lab/latest.md`
- `storage/testing-lab/latest.json`
- مجلد مؤرّخ لكل تشغيل

## إنشاء قاعدة الاختبار (مرة)

```sql
CREATE DATABASE clinic_test_db;
```

ثم:

```powershell
php artisan migrate --env=testing
```

أو دع PHPUnit يشغّل `RefreshDatabase` تلقائياً عند التشغيل.

## الفرق عن السيرفر

| | محلي | VPS إنتاج |
|---|------|-----------|
| `php artisan test` | ✅ | ❌ غالباً (بدون dev) |
| `lab:smoke` | ✅ | ❌ ممنوع |
| بيانات | `clinic_test_db` | `clinic` |
