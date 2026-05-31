# Security-sensitive configuration audit (Phase 0)

Audit date: 2026-05-24. Scope: `config/`, environment templates, and credential handling.

## Critical — remediated in Phase 0

| Item | Location | Finding | Remediation |
|------|----------|---------|-------------|
| Platform owner password in repo | `config/platform.php` (historical) | Plaintext email/password committed to VCS | Values removed; `env('PLATFORM_OWNER_*')` only |
| PHPUnit DB password in repo | `phpunit.xml` (historical) | Local DB password committed | Default changed to `postgres`; CI uses service credentials |

**Action required:** If the old `config/platform.php` credentials were ever pushed to a remote, rotate the platform owner password and audit git history.

## High — addressed or documented

| Item | Location | Finding | Status |
|------|----------|---------|--------|
| Platform email gate | `EnsurePlatformOwner` | Empty email allowed any `super_admin` | Production now requires `PLATFORM_OWNER_EMAIL` (503 if missing) |
| Chat cross-tenant IDOR | `ChatMessage` model | No `TenantScope` | `TenantScope` + `BelongsToClinic` registered |
| Resource IDOR | Patient/Appointment/Invoice controllers | Route permission only | Policies + `$this->authorize()` on mutations |
| `.env.example` | Root | Dev-oriented, incomplete prod guidance | Production checklist + Stripe yearly keys documented |

## Medium — open (future phases)

| Item | Location | Recommendation |
|------|----------|----------------|
| Seeder default passwords | `RolePermissionSeeder` | `admin@clinic.local` / `password` for local dev only; disable or change in production |
| `super_admin` bypasses `TenantScope` | `TenantScope` | Acceptable while platform routes block clinic UI; add integration tests |
| Database-backed cache/session/queue | `config/cache.php`, etc. | Move to Redis in production (Phase 5) |
| No CSP / 2FA / session registry | — | Phase 4 |
| Chat authorization | `InternalChatController` | Tenant scope adds defense-in-depth; controller checks remain |
| Only 2 policies before Phase 0 | `app/Policies/` | Expanded to Patient, Appointment, Invoice, Visit |
| `.env.testing` contains DB password | `.env.testing` | Local file; do not deploy; CI uses workflow env |
| Cashier/Stripe secrets | `.env` | Must be set per environment; never in config files |

## Low — informational

| Item | Notes |
|------|-------|
| `config/cashier.php` | Uses `env()` for Stripe keys — correct pattern |
| `config/tenancy.php` | Only `default_clinic_id` — no secrets |
| `config/services.php` | Standard third-party placeholders |
| `config/backup.php` | Optional tool paths via env |
| CSRF exemption | `stripe/*` only — appropriate for webhooks |
| Login rate limiting | `LoginRequest` — present |

## Files reviewed

- `config/platform.php`, `config/auth.php`, `config/cashier.php`, `config/database.php`
- `config/cache.php`, `config/queue.php`, `config/session.php`, `config/filesystems.php`
- `config/logging.php`, `config/permission.php`, `config/tenancy.php`, `config/backup.php`
- `.env.example`, `.env.testing`, `phpunit.xml`
- `bootstrap/app.php` (CSRF exceptions, middleware aliases)

## Verification

- Run `php artisan test` after changes
- Confirm `config/platform.php` contains no literal credentials
- Confirm production `.env` sets `PLATFORM_OWNER_EMAIL` before enabling platform routes
