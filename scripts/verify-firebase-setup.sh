#!/usr/bin/env bash
# Verify Firebase files for clinic-system (run from repo root)
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "${ROOT}"

OK=0
FAIL=0

check() {
  if eval "$2"; then
    echo "  ✅ $1"
    OK=$((OK + 1))
  else
    echo "  ❌ $1"
    FAIL=$((FAIL + 1))
  fi
}

echo "==> Firebase / Push verification"
echo ""

echo "Backend (Laravel):"
check "storage/app/firebase-credentials.json exists" "[[ -f storage/app/firebase-credentials.json ]]"
check "PUSH_ENABLED=true in .env" "grep -q '^PUSH_ENABLED=true' .env 2>/dev/null"
check "FIREBASE_PROJECT_ID set (or in credentials)" "grep -q '^FIREBASE_PROJECT_ID=.\+' .env 2>/dev/null || jq -e '.project_id' storage/app/firebase-credentials.json >/dev/null 2>&1"

echo ""
echo "Mobile (Expo):"
check "mobile/google-services.json exists" "[[ -f mobile/google-services.json ]]"
check "package com.clinic.system in google-services.json" "grep -q 'com.clinic.system' mobile/google-services.json 2>/dev/null"
check "googleServicesFile in app.json" "grep -q 'googleServicesFile' mobile/app.json"

echo ""
if [[ -f .env ]] && [[ -f storage/app/firebase-credentials.json ]]; then
  echo "Laravel OAuth test:"
  if php artisan push:verify 2>/dev/null; then
    echo "  ✅ push:verify passed"
  else
    echo "  ❌ push:verify failed — run: php artisan push:verify"
    FAIL=$((FAIL + 1))
  fi
fi

echo ""
echo "Result: ${OK} passed, ${FAIL} failed"
if [[ ${FAIL} -gt 0 ]]; then
  echo "See docs/FIREBASE-PUSH-AR.md for setup steps"
  exit 1
fi

echo "All checks passed — ready to build APK and deploy"
