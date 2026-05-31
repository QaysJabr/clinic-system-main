#!/usr/bin/env bash
# Fix PostgreSQL credential mismatch between .env and Docker volume
set -euo pipefail
cd /var/www/clinic-system-main

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"

echo "==> Current .env DB settings:"
grep '^DB_' .env || true

# Docker postgres uses these — Laravel must match exactly
sed -i 's|^DB_CONNECTION=.*|DB_CONNECTION=pgsql|' .env
sed -i 's|^DB_HOST=.*|DB_HOST=postgres|' .env
sed -i 's|^DB_PORT=.*|DB_PORT=5432|' .env

if grep -q '^DB_DATABASE=.\+' .env; then
  DB_NAME="$(grep '^DB_DATABASE=' .env | cut -d= -f2-)"
else
  DB_NAME=clinic
  echo "DB_DATABASE=${DB_NAME}" >> .env
fi

if [[ "${DB_NAME}" != "clinic" ]] || grep -q '^DB_USERNAME=postgres' .env; then
  echo "    Fixing DB_DATABASE=clinic and DB_USERNAME=clinic"
  sed -i 's|^DB_DATABASE=.*|DB_DATABASE=clinic|' .env
  sed -i 's|^DB_USERNAME=.*|DB_USERNAME=clinic|' .env
fi

if grep -q '^DB_PASSWORD=.\+' .env && ! grep -q 'CHANGE_ME' .env; then
  echo "    Keeping existing DB_PASSWORD"
else
  DB_PASS="$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 24)"
  sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASS}|" .env || echo "DB_PASSWORD=${DB_PASS}" >> .env
  echo "    Generated new DB_PASSWORD"
fi

echo ""
echo "==> New .env DB settings:"
grep '^DB_' .env

echo ""
echo "==> Recreate PostgreSQL volume (fresh DB with matching password)"
${COMPOSE} down
docker volume rm clinic-system-main_pgdata 2>/dev/null || true

echo "==> Start containers"
${COMPOSE} up -d
echo "    Waiting for postgres..."
sleep 12

echo "==> Migrate"
${COMPOSE} exec -T app php artisan config:clear
${COMPOSE} exec -T app php artisan migrate --force

# Ensure seed passwords exist (min 12 chars for production)
if ! grep -q '^SEED_ADMIN_PASSWORD=.\{12,\}' .env 2>/dev/null; then
  ADMIN_PASS="$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)"
  if grep -q '^SEED_ADMIN_PASSWORD=' .env 2>/dev/null; then
    sed -i "s|^SEED_ADMIN_PASSWORD=.*|SEED_ADMIN_PASSWORD=${ADMIN_PASS}|" .env
  else
    echo "SEED_ADMIN_PASSWORD=${ADMIN_PASS}" >> .env
  fi
  echo "    Generated SEED_ADMIN_PASSWORD"
  echo "    Clinic admin: admin@clinic.local / ${ADMIN_PASS}"
fi
if ! grep -q '^PLATFORM_OWNER_PASSWORD=.\{12,\}' .env 2>/dev/null; then
  OWNER_PASS="$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)"
  sed -i "s|^PLATFORM_OWNER_PASSWORD=.*|PLATFORM_OWNER_PASSWORD=${OWNER_PASS}|" .env
  echo "    Generated PLATFORM_OWNER_PASSWORD"
  echo "    Platform owner: $(grep '^PLATFORM_OWNER_EMAIL=' .env | cut -d= -f2-) / ${OWNER_PASS}"
fi

${COMPOSE} exec -T app php artisan config:clear
${COMPOSE} exec -T app php artisan db:seed --class=RolePermissionSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=PlanSeeder --force
${COMPOSE} exec -T app php artisan db:seed --class=ExpenseCategorySeeder --force
${COMPOSE} exec -T app php artisan config:cache

echo ""
echo "Done. DB user=clinic, database=clinic"
