# Security — Credential Rotation & Secret Exposure

## Git history audit (action required)

The repository may contain **test-only** credentials in tracked files. Treat any value that ever appeared in git history as **potentially exposed**, even after removal.

| Asset | Location | Risk | Rotation action |
|-------|----------|------|-----------------|
| `APP_KEY` | `.env.testing`, CI `APP_KEY` | Test placeholder only | **Rotate production `APP_KEY`** if a production key was ever committed |
| `DB_PASSWORD` | `phpunit.xml` (`0027`), CI (`postgres`) | Local/CI only | Rotate **production** DB password; use secrets manager |
| `PLATFORM_OWNER_PASSWORD` | `.env.example`, seeders, CI | Default `password` | **Mandatory** change on first production deploy |
| Seeded admin | `admin@clinic.local` / `password` | Known pair | Disable or change password immediately after seed |
| Stripe keys | `.env` (not committed) | High if leaked | Rotate in Stripe Dashboard |
| `HEALTH_CHECK_TOKEN` | `.env` | Medium | Rotate if exposed |
| Redis password | `.env` | Medium | Rotate if exposed |
| 2FA secrets | DB column `two_factor_secret` | Per-user | Users re-enroll 2FA if DB dump leaked |

## Verify no production secrets in repo

```bash
# Search for likely secrets (run before each release)
rg -i "(sk_live|sk_test|api[_-]?key|BEGIN (RSA|OPENSSH)|password\s*=\s*[^$])" --glob "!vendor" --glob "!.env"
```

**Policy:** All sensitive values must come from environment variables or secret stores — never from `config/*.php` literals.

## Post-deploy rotation checklist

1. `APP_KEY` — only before go-live if compromised; invalidates sessions
2. Database user password
3. `PLATFORM_OWNER_PASSWORD` + clinic admin passwords
4. Stripe `STRIPE_SECRET` + `STRIPE_WEBHOOK_SECRET`
5. `HEALTH_CHECK_TOKEN`, `REDIS_PASSWORD`, mail credentials
6. Revoke old trusted devices / sessions (`user_sessions`, `trusted_devices`)

## Production seeding

- Set `SEED_ALLOW_INSECURE=false` (default)
- Provide `PLATFORM_OWNER_PASSWORD` (≥12 chars, not in forbidden list)
- Optionally `SEED_ADMIN_PASSWORD` for clinic admin seed
- Never run demo seeders unless `SEED_DEMO_DATA=true` on intentional demo stacks
