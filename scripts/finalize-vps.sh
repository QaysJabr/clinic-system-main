#!/usr/bin/env bash
# Fix 500 after deploy: APP_KEY, .env sync, cache, seed
set -euo pipefail
cd /var/www/clinic-system-main

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Ensure seed passwords in .env"
if ! grep -q '^SEED_ADMIN_PASSWORD=.\{12,\}' .env 2>/dev/null; then
  ADMIN_PASS="$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)"
  grep -q '^SEED_ADMIN_PASSWORD=' .env && sed -i "s|^SEED_ADMIN_PASSWORD=.*|SEED_ADMIN_PASSWORD=${ADMIN_PASS}|" .env || echo "SEED_ADMIN_PASSWORD=${ADMIN_PASS}" >> .env
  echo "    admin@clinic.local password: ${ADMIN_PASS}"
fi
if ! grep -q '^PLATFORM_OWNER_PASSWORD=.\{12,\}' .env 2>/dev/null; then
  OWNER_PASS="$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)"
  sed -i "s|^PLATFORM_OWNER_PASSWORD=.*|PLATFORM_OWNER_PASSWORD=${OWNER_PASS}|" .env
  echo "    owner@clinic.local password: ${OWNER_PASS}"
fi

echo "==> Restart with mounted .env"
${COMPOSE} up -d
sleep 5

echo "==> APP_KEY"
if ! grep -qE '^APP_KEY=base64:' .env; then
  ${COMPOSE} exec -T app php artisan key:generate --force
fi

echo "==> Restart PHP workers (pick up APP_KEY)"
${COMPOSE} restart app queue scheduler
sleep 3

echo "==> Clear & rebuild cache"
${COMPOSE} exec -T app php artisan config:clear
${COMPOSE} exec -T app php artisan cache:clear
${COMPOSE} exec -T app php artisan view:clear
${COMPOSE} exec -T app php artisan route:clear

echo "==> Seed roles/users"
${COMPOSE} exec -T app php artisan db:seed --class=RolePermissionSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=PlanSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=ExpenseCategorySeeder --force

${COMPOSE} exec -T app php artisan config:cache
${COMPOSE} exec -T app php artisan route:cache
${COMPOSE} exec -T app php artisan view:cache

echo ""
echo "==> Health check"
curl -s -o /dev/null -w "HTTP /up → %{http_code}\n" http://127.0.0.1/up || true
curl -s -o /dev/null -w "HTTP / → %{http_code}\n" http://127.0.0.1/ || true

echo ""
echo "If still 500, show last error:"
echo "  tail -30 storage/logs/laravel.log"
