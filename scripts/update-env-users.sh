#!/usr/bin/env bash
# Apply PLATFORM_* / SEED_* from .env to database users (after editing .env).
# Usage: bash scripts/update-env-users.sh
set -euo pipefail
cd /var/www/clinic-system-main

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Rebuild app (apply latest seeders from git)"
${COMPOSE} up -d --build
sleep 5

echo "==> Reload .env into Laravel"
${COMPOSE} exec -T app php artisan config:clear
${COMPOSE} exec -T app php artisan cache:clear
${COMPOSE} exec -T app php artisan route:clear
${COMPOSE} exec -T app php artisan view:clear

echo "==> Sync users from .env"
${COMPOSE} exec -T app php artisan db:seed --class=RolePermissionSeeder --force

${COMPOSE} exec -T app php artisan config:cache
${COMPOSE} exec -T app php artisan route:cache
${COMPOSE} exec -T app php artisan view:cache

echo ""
echo "==> Health check"
curl -s -o /dev/null -w "HTTP /up → %{http_code}\n" http://127.0.0.1:${APP_PORT:-80}/up || true
curl -s -o /dev/null -w "HTTP /login → %{http_code}\n" http://127.0.0.1:${APP_PORT:-80}/login || true
curl -s -o /dev/null -w "HTTP /api/v1/meta → %{http_code}\n" http://127.0.0.1:${APP_PORT:-80}/api/v1/meta || true

echo ""
echo "Done. Login with the emails/passwords from your .env:"
grep -E '^PLATFORM_OWNER_EMAIL=|^SEED_ADMIN_EMAIL=' .env || true
echo ""
echo "Web:   http://31.97.61.205/login"
echo "Mobile API uses the same credentials."
