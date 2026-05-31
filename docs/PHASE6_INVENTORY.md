# Phase 6 — Clinic Inventory & Medical Supplies

Lightweight, tenant-scoped inventory for consumables and clinic supplies. Not a hospital ERP.

## Architecture summary

| Layer | Responsibility |
|-------|----------------|
| **Models** | `InventoryItem`, categories, units, suppliers, procedure templates, purchases, stock movements — all `BelongsToClinic` + global tenant scope |
| **Support** | `InventoryMovementType`, `InventoryItemStatus`, `InventoryNameNormalizer` |
| **Services** | `InventoryStockMovementService` (single write path), `InventoryProcedureConsumptionService`, `InventoryPurchaseReceiveService`, `InventoryDashboardService`, `InventoryAlertService`, `ClinicInventorySetupService` |
| **Controllers** | Thin `Inventory\*Controller` extending `InventoryController` (uses `AuthorizesRequests`) |
| **Policy** | `InventoryItemPolicy` — view vs manage via `view inventory` / `manage inventory` |
| **Hook** | `VisitObserver` — on visit `completed`, run procedure consumption once per visit |

## Database schema

- **inventory_categories** / **inventory_units** — per-clinic catalogs (seeded defaults on first dashboard visit)
- **inventory_suppliers** — optional vendor directory
- **inventory_items** — SKU, qty on hand, minimum, status, expiry, batch, unit cost, barcode placeholder
- **inventory_procedure_templates** + **inventory_procedure_template_items** — procedure name → items/qty (normalized name match)
- **inventory_purchases** + **inventory_purchase_lines** — receive stock (creates `stock_in` movements)
- **inventory_stock_movements** — audit trail: type, delta, qty before/after, visit_id, purchase_id, user_id, notes

Future-ready columns: `barcode` on items; `clinic_id` on all tables for multi-warehouse expansion later.

## Workflows

### Stock changes

All quantity changes go through `InventoryStockMovementService::record()` inside a DB transaction with row lock.

Types: `stock_in`, `stock_out`, `adjustment`, `damaged`, `expired`, `consumption`, `correction`.

### Procedure consumption

1. Define template: procedure **name** (must match `VisitProcedure.name` on completed visit).
2. When visit status becomes `completed`, `InventoryProcedureConsumptionService`:
   - Skips if consumption movements already exist for that `visit_id`
   - For each visit procedure, finds active template by `name_normalized`
   - Deducts template line quantities; logs `consumption` movements
   - On insufficient stock, logs failure and notifies (`INVENTORY_CONSUMPTION_FAILED`)

### Purchases

`InventoryPurchaseReceiveService` creates purchase + lines and `stock_in` movements.

### Alerts

`InventoryAlertService` — low stock (qty ≤ minimum), expiring within 30 days, consumption failures — via existing `InAppNotificationService`.

## Dashboard

`/inventory` — KPIs, low stock / expiring lists, recent movements, top consumed (30d), quick actions. Matches SaaS layout and dark mode.

## Reports

`/inventory/reports` — valuation snapshot, low stock list, expiry list, recent movements (filterable by type in query string).

## Performance

- Eager load `category`, `unit`, `supplier` on item lists
- Paginate indexes (20)
- Movement writes use `lockForUpdate` on item row
- Dashboard aggregates use scoped queries; avoid N+1 on movement lists with `item:id,name`

## Finance integration

- On **receive stock**, optional **Record as clinic expense** creates a paid `Expense` linked via `inventory_purchases.expense_id`.
- Default expense category **Inventory & supplies** is seeded per clinic (`ClinicInventorySetupService`).
- Purchase list shows total and link to expense when posted.

## UI navigation

Sub-nav on all inventory pages: Overview, Items, Movements, Purchases, Suppliers, Consumption rules, Reports.

## Future expansion (not implemented)

- Barcode / QR scanning
- Multi-warehouse (`warehouse_id` on movements)
- Reorder suggestions / vendor APIs
- Pharmacy module
- Central procurement

## Permissions

- `manage inventory` — admin, receptionist (full CRUD, movements, purchases)
- `view inventory` — accountant (read dashboard, items, reports)

## Tests

`tests/Feature/InventoryModuleTest.php` — dashboard access, stock deduction, procedure consumption, cross-tenant isolation.

## Key routes

- `inventory.dashboard`, `inventory.items.*`, `inventory.movements.*`, `inventory.templates.*`, `inventory.purchases.*`, `inventory.suppliers.*`, `inventory.reports.index`
