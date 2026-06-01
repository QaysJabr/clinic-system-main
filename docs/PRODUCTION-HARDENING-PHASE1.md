# Production Hardening — Phase 1 (completed)

## Security & tenancy

- **Public booking** (`PublicAppointmentBookingController`): doctor must belong to booking clinic; patient creation respects plan limits; booking runs in a DB transaction with row locks; routes throttled via `booking-public`.
- **Patient portal tokens**: default TTL 90 days (`PATIENT_PORTAL_TOKEN_TTL_DAYS`).
- **Subscription checks**: `SubscriptionService::isActive()` no longer treats “active” clinic status alone as sufficient; requires usable `clinic_subscriptions` row or a future `subscription_expires_at`. Platform **activate** sets expiry and creates subscription when a plan is assigned.

## Concurrency

- **Expense installments**: `ExpensePaymentController::store` uses transaction + `lockForUpdate` on expense.
- **Appointment check-in**: reloads appointment with `lockForUpdate` inside transaction.
- **Inventory consumption**: visit locked inside transaction; idempotency check after lock.

## API / middleware

- **CheckSubscription**: JSON 403 with `code: subscription_inactive` for API clients.

## Tests & CI

- Fixed 5 previously failing tests (registration terms, locale reload, 2FA config, subscription reminders).
- Added `.github/workflows/tests.yml` (PHPUnit on PostgreSQL).

## Deploy notes

```bash
git pull
bash scripts/fix-all-production.sh
php artisan config:cache
php artisan route:cache
```

Optional env:

- `PATIENT_PORTAL_TOKEN_TTL_DAYS=90`
- `SCHEDULING_PUBLIC_BOOKING_RATE_LIMIT=30`
- `SECURITY_2FA_ENABLED=true` (when ready)

## Phase 2 (completed in repo)

- API tests: patients, invoices, payments, subscription JSON 403.
- Public booking security test (cross-clinic doctor rejected).
- Mobile `env.ts` reads `expo.extra.apiBaseUrl` / `EXPO_PUBLIC_API_URL` (see `mobile/app.config.js`).
- Operator checklist: `docs/DEPLOY-YOU-DO-THIS-AR.md` (server + `.env` only).

## Still optional

- Enable 2FA on server (`SECURITY_2FA_ENABLED=true`).
- Horizon package if using queue dashboard in Docker.
- HTTPS + domain; Practitioner layer (product decision).
