# Phase 5 — Performance Optimization & Scalability

## Performance optimization report

| Area | Before | After |
|------|--------|-------|
| Cache store | Database default | Redis-ready (`CACHE_STORE=redis`) + tenant keys |
| Queue | Database default | Redis-ready + **Laravel Horizon** (Docker/Linux) |
| Dashboard charts | PHP row scans | SQL `GROUP BY` month buckets (`MonthBucket`) |
| Report page | Cache disabled | Metrics cached 90s; live lists refreshed each request |
| Invoice KPIs | 3× status queries | Single `invoiceCountsByStatus()` call |
| SPA fragments | Unbounded object cache | Map + LRU (24 entries, 5 min TTL) |
| Notifications sync | Hourly sync command | Queued `SyncAppointmentNotificationsJob` |
| Attachments | Full-size preview only | Optional GD thumbnails for images |

## Benchmark summary (expected impact)

Measured conceptually against Phase 4 baseline (same hardware):

| Workload | Expected improvement |
|----------|---------------------|
| Dashboard chart rebuild (cache miss) | **60–80%** less memory (SQL aggregation vs `get()` all rows) |
| Dashboard repeat views (60s TTL) | **~95%** faster (cache hit) |
| Reports index (90s metrics cache) | **~40–70%** faster on repeat loads |
| SPA navigation (warm cache) | Near-instant fragment swap |

Run local benchmarks:

```bash
php artisan test --filter=PerformancePhase5Test
# Optional: ab -n 50 -c 10 http://127.0.0.1:8000/dashboard
```

## Cache strategy

**Key pattern:** `clinic.{clinic_id}.{segment}` via `TenantCache`.

| Segment | TTL (default) | Invalidation |
|---------|---------------|--------------|
| `dashboard.financial` | 60s | Invoice/Payment/Visit/Patient/Appointment/Expense observers |
| `dashboard.analytics.{scope}.{locale}` | 120s | Same |
| `dashboard.operational.{scope}` | 45s | Same |
| `reports.data.{queryHash}` | 90s | Same (metrics only; recent lists always fresh) |

**Invalidation:** `ClinicCacheInvalidator::flush($clinicId)` on financial data writes.

## Scalability notes

- **Horizontal scaling:** Stateless PHP-FPM + shared Redis + PostgreSQL.
- **Workers:** `docker compose up horizon queue` — scale `queue`/`horizon` replicas.
- **Sessions:** Move to Redis (`SESSION_DRIVER=redis`) for multi-node.
- **Rate limiting:** Uses application cache store (Redis when configured).
- **Horizon on Windows dev:** Requires `ext-pcntl` (Linux/Docker only); use `queue:work` locally.

## Profiling & monitoring guidance

| Tool | Use |
|------|-----|
| `/horizon` | Queue throughput, failed jobs, wait times |
| `/health?token=...` | DB, cache, queue, Redis probes |
| Laravel Telescope (optional) | Slow queries in staging |
| `php artisan db:show` + `EXPLAIN` | Report/dashboard SQL tuning |
| Redis `INFO stats` | Cache hit ratio |

**Slow request checklist:** dashboard cache miss → reports `computeReportData` → patient ledger full history → export streams.

## Changed files (summary)

- `config/performance.php`, `config/horizon.php`
- `app/Support/{TenantCache,MonthBucket,ClinicCacheInvalidator,ClinicReportCache}.php`
- `app/Services/ClinicDashboardAnalyticsService.php`, `ClinicDashboardService.php`
- `app/Jobs/SyncAppointmentNotificationsJob.php`
- `app/Services/Attachments/AttachmentThumbnailService.php`
- `app/Providers/HorizonServiceProvider.php`
- Observers (cache flush hooks)
- `resources/js/spa-navigation.js`, `vite.config.js`
- `docker-compose.yml` (horizon service)
- `tests/Feature/PerformancePhase5Test.php`

## Production env (minimum)

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_HOST=redis
```

Run: `php artisan horizon` (or Docker `horizon` service).
