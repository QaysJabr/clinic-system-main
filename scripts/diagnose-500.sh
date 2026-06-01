#!/usr/bin/env bash
# Diagnose HTTP 500 on production — run on VPS:
#   cd /var/www/clinic-system-main && bash scripts/diagnose-500.sh
set -uo pipefail
cd "$(dirname "$0")/.."

COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
PORT="$(grep '^APP_PORT=' .env 2>/dev/null | cut -d= -f2- || echo 80)"

echo "=== Clinic System — 500 diagnostic ==="
echo ""

echo "1) Docker services"
${COMPOSE} ps
echo ""

echo "2) .env sanity (common typos)"
if grep -q 'PUSH_QUEUE=defaultMOBILE' .env 2>/dev/null; then
  echo "   ERROR: PUSH_QUEUE line is merged — fix to PUSH_QUEUE=default on its own line"
fi
if grep -q '<<<' .env 2>/dev/null; then
  echo "   ERROR: .env still has placeholder text <<< — replace with real values"
fi
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  echo "   WARN: APP_KEY missing or invalid"
fi
echo ""

echo "3) Redis ping (session/cache need this)"
${COMPOSE} exec -T redis redis-cli ping 2>/dev/null || echo "   FAIL: redis container or redis-cli"
echo ""

echo "4) DB connection"
${COMPOSE} exec -T app php artisan db:show 2>&1 | head -20 || echo "   FAIL: database"
echo ""

echo "5) Last Laravel log lines"
LOG="storage/logs/laravel.log"
if [[ -f "$LOG" ]]; then
  tail -n 40 "$LOG"
else
  ${COMPOSE} exec -T app sh -c 'tail -n 40 storage/logs/laravel.log 2>/dev/null' || echo "   No log file yet"
fi
echo ""

echo "6) HTTP from inside server"
for path in /up /api/v1/meta /; do
  code="$(curl -s -o /dev/null -w '%{http_code}' "http://127.0.0.1:${PORT}${path}" 2>/dev/null || echo 000)"
  echo "   ${path} → HTTP ${code}"
done
echo ""

echo "=== Quick fix (try if 500 persists) ==="
echo "  ${COMPOSE} exec -T app php artisan optimize:clear"
echo "  ${COMPOSE} exec -T app php artisan config:cache"
echo "  bash scripts/fix-all-production.sh"
