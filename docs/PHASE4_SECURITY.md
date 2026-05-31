# Phase 4 — Security Hardening & DevOps

## Security audit summary

| Area | Before | After Phase 4 |
|------|--------|----------------|
| 2FA | None | TOTP (authenticator), recovery codes, trusted devices, admin/clinic enforcement |
| Sessions | DB driver only | Active sessions UI, revoke, IP/UA tracking middleware |
| Authorization | 5 policies | + User, Expense, Staff policies; chat route gated |
| CSP / headers | None | CSP (nonce-aware), X-Frame-Options, nosniff, HSTS (HTTPS), Referrer-Policy |
| Audit | Broad CRUD | Dedicated `AuditLogger::security()` for auth, roles, attachments |
| Uploads | Public disk, mimes rule | Private `local` disk by default, MIME/extension validation, malware scanner hook |
| DevOps | VPS docs only | Docker Compose, Dockerfile, queue + scheduler services |
| CI | test + pint + build | + deploy artifact workflow; composer audit recommended |
| Observability | `/up` only | `/health` (DB, cache, queue, Redis), Sentry-ready via env |

## Deployment readiness

**Ready for staging** with Docker Compose and documented env vars.

**Before production:**

1. Set `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`
2. Enable `SECURITY_CSP_ENABLED=true`; test Vite assets after build
3. Enforce 2FA for admins: `SECURITY_2FA_ENFORCE_PLATFORM_OWNER=true` + clinic `require_two_factor`
4. Use PostgreSQL + Redis (`CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`)
5. Configure `HEALTH_CHECK_TOKEN` and monitor `GET /health?token=...`
6. Optional: `SENTRY_LARAVEL_DSN` (install `sentry/sentry-laravel` when needed)
7. Run queue worker + scheduler (see `docker-compose.yml`)

## Infrastructure overview

```
[Browser] → [Nginx] → [PHP-FPM app]
                ↓
    [PostgreSQL]  [Redis] ← cache / queue / sessions
                ↓
    [queue worker] [scheduler loop]
```

## Remaining risks

| Risk | Mitigation |
|------|------------|
| Legacy attachments on `public` disk | New uploads use `local`; migrate old paths in a future maintenance window |
| Email verification not enforced on `User` model | Routes use `verified`; enable `MustVerifyEmail` when mail is production-ready |
| Malware scanning | Implement `App\Contracts\MalwareScanner` and set `SECURITY_MALWARE_SCANNER` |
| Sentry not in composer by default | Add package when DSN is available |
| CSP + Alpine + Vite | Dev (`APP_DEBUG=true`): relaxed script/style for HMR. Prod: script nonce + `unsafe-eval` (Alpine), `style-src-attr` for Tailwind attrs, Google Fonts on `style-src-elem` |

## Changed files (summary)

- **Migrations:** `database/migrations/2026_05_26_100000_phase4_security_foundation.php`
- **Config:** `config/security.php`
- **Services:** `app/Services/Security/*`
- **Middleware:** `SecurityHeaders`, `EnsureTwoFactorVerified`, `TrackActiveSession`
- **Controllers:** `app/Http/Controllers/Security/*`, `HealthController`
- **Policies:** `UserPolicy`, `ExpensePolicy`, `StaffPolicy`
- **Routes:** `routes/security.php`
- **Views:** `resources/views/security/**`, profile security card
- **Lang:** `resources/lang/{ar,en}/security.php`
- **Docker:** `Dockerfile`, `docker-compose.yml`, `docker/nginx/default.conf`
- **CI:** `.github/workflows/deploy.yml`
- **Tests:** `tests/Feature/SecurityPhase4Test.php`

## Environment variables

See `.env.example` section `--- Security (Phase 4) ---`.

## Queue & scheduler

```bash
# Docker Compose (included)
docker compose up -d queue scheduler

# Manual
php artisan queue:work --tries=3
# Cron: * * * * * php /path/to/artisan schedule:run
```
