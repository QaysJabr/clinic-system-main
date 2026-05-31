# Production deployment guide

This document covers a minimal, secure deployment for the clinic-system Laravel SaaS application on a traditional Linux host (VPS, Forge, etc.). For container-based deployment, see Phase 4 of the architecture roadmap.

## Requirements

- PHP 8.3+ with extensions: `pdo_pgsql` (or `pdo_mysql`), `mbstring`, `xml`, `ctype`, `fileinfo`, `openssl`, `tokenizer`, `gd` or `imagick` (if image handling is added later)
- PostgreSQL 14+ (recommended; matches CI/tests) or MySQL 8+
- Node.js 20+ (build assets once per release)
- Composer 2.x
- Web server (Nginx/Apache) pointing document root to `public/`
- Cron for Laravel scheduler
- Optional: Redis for cache/session/queue at scale

## Initial setup

1. Clone the repository and install dependencies:

   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci
   npm run build
   ```

2. Configure environment:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Set production values in `.env` (see checklist in `.env.example`):

   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`
   - `PLATFORM_OWNER_EMAIL` and `PLATFORM_OWNER_PASSWORD` (never commit these)
   - Database credentials (`DB_CONNECTION=pgsql` recommended)
   - Stripe live keys and webhook secret
   - Real mail transport

4. Run migrations and seed platform/clinic roles:

   ```bash
   php artisan migrate --force
   php artisan db:seed --class=RolePermissionSeeder
   ```

   Remove or rotate `PLATFORM_OWNER_PASSWORD` from `.env` after seeding if you manage the account password elsewhere.

5. Optimize Laravel:

   ```bash
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. Set filesystem permissions so `storage/` and `bootstrap/cache/` are writable by the web server user.

## Web server

- Document root: `/path/to/clinic-system/public`
- Pass PHP requests to PHP-FPM
- Deny access to `.env`, `storage/logs`, and other sensitive paths outside `public/`

## Scheduler (required)

Appointment reminder sync runs hourly:

```cron
* * * * * cd /path/to/clinic-system && php artisan schedule:run >> /dev/null 2>&1
```

## Queue worker (optional today; recommended when jobs are queued)

If `QUEUE_CONNECTION` is `database` or `redis`:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Use Supervisor or systemd to keep the worker running.

## Stripe webhooks

- Configure Cashier webhook URL: `https://your-domain.example/stripe/webhook`
- Set `STRIPE_WEBHOOK_SECRET` in `.env`
- CSRF is already excluded for `stripe/*` in `bootstrap/app.php`

## Backups

- CLI: `php artisan backup:database` (see `config/backup.php` for tool paths)
- UI: available to users with backup permission
- **Also** schedule off-server backups (volume snapshots or `pg_dump` to object storage)

## Health check

- Laravel exposes `GET /up` for load balancers and uptime monitors

## CI

GitHub Actions runs tests and `npm run build` on push/PR (see `.github/workflows/ci.yml`).

## Post-deploy verification

1. `GET /up` returns healthy
2. Platform owner can log in at `/login` and reach `/platform/dashboard`
3. Clinic user can log in and reach `/dashboard` with active subscription
4. Stripe webhook receives test events (Dashboard → Webhooks)

## Security reminders

- Never commit `.env` or real credentials
- Rotate platform owner password if it was ever stored in git history
- See `docs/SECURITY_CONFIG_AUDIT.md` for config-related findings
