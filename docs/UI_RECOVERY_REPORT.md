# UI/UX Recovery — Root Cause Analysis & Stabilization

**Date:** 2026-05-24  
**Scope:** Frontend architecture audit and visual-system recovery (no new features).

---

## Executive summary

The broken UI was not caused by a missing Vite bundle or a failed production build. The primary failures were **architectural drift**: typography and shell layout were defined in three competing places (Tailwind config, `app.css`, and inline `<style>` blocks in Blade layouts), and a **layout refactor** moved the fixed sidebar outside `#locale-swap-root` while some comments/CSS still assumed inline shell rules in layouts. Secondary issues included **SPA DOM swaps** without Alpine teardown (fixed earlier), **stale SPA HTML cache** after layout changes, and **split `@layer components` blocks** in `app.css` that made the design system harder to reason about.

This pass unifies fonts, centralizes scrollbars and shell rules in `app.css`, removes destructive `* { font-family }` overrides, aligns Tailwind `font-sans` with Cairo, and documents remaining debt.

---

## 1. Root causes (by symptom)

### Broken / inconsistent typography

| Factor | Impact |
|--------|--------|
| `tailwind.config.js` set `fontFamily.sans` to **Figtree** (never loaded) | `font-sans` utilities pointed at a missing font |
| Google Fonts loaded **Cairo** in layouts | Actual UI used Cairo |
| Inline `* { font-family: 'Cairo' }` in `app.blade.php`, `platform.blade.php`, `guest.blade.php` | Overrode Tailwind utilities and broke monospace/icon fallbacks |
| `body { font-family: 'Cairo' }` duplicated in `app.css` | Third source of truth |

**Fix:** Cairo as `theme.fontFamily.sans`; `body` uses `@apply font-sans`; remove universal `*` selector; shared `partials/layout-vite-assets.blade.php`.

### Missing or partial component styles

| Factor | Impact |
|--------|--------|
| Dashboard widgets use `.dash-*` classes in `@layer components` in `app.css` | Safe from purge when CSS is built — **not** a purge issue if `npm run build` ran |
| Stale `public/build` or browser cache | Old CSS hash served after changes |
| Inline layout `<style>` removed before shell rules fully lived in `app.css` | Brief window of unstyled shell (now consolidated) |

**Fix:** Shell + dashboard rules confirmed in `app.css`; production build required after CSS edits.

### Inconsistent spacing / dashboard alignment

| Factor | Impact |
|--------|--------|
| Sidebar moved to `<body>` with `.main-with-sidebar { margin-inline-start: 272px }` | Correct pattern; previous `overflow-x-hidden` on `#locale-swap-root` broke `position:fixed` |
| Nested `max-width` on `#app-content` inner wrapper + `.dash-shell max-w-[1440px]` | Acceptable double constraint; normalized with `.app-content-inner` |
| Inline `style="margin-bottom: 24px"` on page headers | Inconsistent with Tailwind rhythm |

**Fix:** `.app-content-inner` / `.app-page-header` utilities; sidebar remains outside SPA swap root.

### CSS hierarchy / override conflicts

| Factor | Impact |
|--------|--------|
| Inline layout styles **after** `@vite(app.css)` | Higher specificity for `*` and scrollbars vs bundled CSS |
| Duplicate scrollbar rules (layout inline + partial dark rules in `app.css`) | Inconsistent light-mode scrollbars |
| Two `@layer components { }` blocks in `app.css` | Valid in Tailwind 3 but confusing; dashboard block kept, guest auth extracted to `guest-auth.css` |

### Tailwind / Vite / asset pipeline

| Item | Status |
|------|--------|
| Stack | **Tailwind 3** + PostCSS (`postcss.config.js`), not `@tailwindcss/vite` v4 (listed in `package.json` but unused) |
| CSS entry | Single: `resources/css/app.css` via `@vite` in layouts |
| Vite entries | `app.css`, `app.js`, plus page-specific chunks — correct |
| `content` paths | Was views-only; **JS-generated class strings** (e.g. `admin-plans.js`) risked purge — extended to `resources/js/**/*.js` |
| `darkMode` | `class` on `<html>` — consistent with `theme.js` |

### SPA navigation / Alpine

| Factor | Impact |
|--------|--------|
| Fragment swap on `#app-content` without `destroyTree` | Stale Alpine state (mitigated via `alpine-swap-utils.js`) |
| Confirm dialog `title` / `open` on swapped DOM | Empty modal (mitigated via Alpine store + `clinic:close-confirm` on navigate) |
| `spaCache` (5 min TTL) | Can serve **old HTML** with pre-refactor layout until TTL expires or hard refresh |
| Header notifications | Re-bound on `spa:navigated` (`header-notifications.js`) |
| Dashboard charts | Dynamic `import('./clinic-dashboard.js')` on `spa:navigated` in `spa-navigation.js` |

**Recommendation:** After layout changes, hard-refresh once or wait for SPA cache TTL; optional future: bump cache key on deploy.

---

## 2. Frontend architecture summary

```
Blade layouts (app | platform | guest)
  └── partials/layout-vite-assets.blade.php  → Cairo fonts + @vite(app.css, app.js)
  └── app.css (@tailwind + @layer base/components + guest-auth import)
  └── tailwind.config.js (darkMode: class, content: views + js)
  └── vite.config.js → laravel-vite-plugin (multi JS entries)

App shell (clinic + platform)
  <aside id="sidebar.app-sidebar">     ← fixed, outside SPA swap
  <div id="locale-swap-root">
    <div class="main-with-sidebar">
      <nav> … header …
      <main id="app-content"> … $slot …

SPA (spa-navigation.js)
  fragment → #app-content innerHTML + alpineDestroyTree/initTree + spa:navigated
  shell    → #locale-swap-root innerHTML (main column only; sidebar persists)

Design tokens (CSS)
  font-sans → Cairo
  .sidebar-*, .clinic-*, .dash-*, .card, .btn-*
```

---

## 3. CSS / Tailwind fixes (this pass)

- `tailwind.config.js`: Cairo `fontFamily.sans`, content includes `resources/js/**/*.js`
- `app.css`: `font-sans` on body, full scrollbar rules in `@layer base`, `.app-content-inner`, updated shell comment
- `resources/css/guest-auth.css`: auth page styles (removed from guest layout inline block)
- `partials/layout-vite-assets.blade.php`: shared font + Vite includes
- Layouts: removed inline `*` font and duplicate scrollbars; softened `html` overflow class

---

## 4. SPA stabilization summary

| Check | Status |
|-------|--------|
| Alpine destroy/init on swap | OK (`alpine-swap-utils.js`) |
| Confirm dialog close on navigate | OK (`clinic:close-confirm`) |
| Header notifications re-bind | OK (`spa:navigated`) |
| Flash auto-dismiss on swap | OK (`spaApplyPayload`) |
| Sidebar mobile menu | Delegated on `document` — survives swaps |
| Auth paths excluded from SPA | OK (`spaIsAuthBoundaryPath`) |

---

## 5. Design consistency report

| Area | Target | After pass |
|------|--------|------------|
| Typography | Single Cairo stack, RTL-safe | Unified via Tailwind `font-sans` |
| Spacing | Card/section rhythm | Dashboard `.dash-*` + `.app-content-inner` |
| Shell | Fixed sidebar + main offset | CSS in `app.css` `.main-with-sidebar` |
| Dark mode | `class` on `html` | Unchanged |
| RTL | Logical properties (`margin-inline-start`, `border-s`) | Unchanged |
| Auth pages | Premium card, no stretch | `guest-auth.css` |

**Visual QA checklist (manual):**

- [ ] `/login`, `/register-clinic` — fields, dark mode, submit
- [ ] `/dashboard` — widgets, charts, sidebar, RTL
- [ ] SPA navigate patients → invoices → back
- [ ] Platform admin dashboard
- [ ] Hard refresh vs SPA navigation after deploy

---

## 6. Remaining UI technical debt

1. **`@tailwindcss/vite` v4** in `package.json` unused — remove or migrate deliberately later.
2. **SPA in-memory cache** may serve outdated fragments after deploy — consider `Cache-Control` or versioned cache key.
3. **Second `@layer components` block** for dashboard — could merge into one block for maintainability.
4. **Guest layout** still has structural inline styles on `body` (`margin: 0`) — minor.
5. **Platform/clinic brand colors** split between arbitrary hex and Tailwind palette — future token file (`--brand-primary`).
6. **admin-plans / platform-clinics** separate Vite entries — ensure `@vite` on those pages only where needed.

---

## 7. Changed files (this pass)

- `docs/UI_RECOVERY_REPORT.md` (this document)
- `tailwind.config.js`
- `resources/css/app.css`
- `resources/css/guest-auth.css` (new)
- `resources/views/partials/layout-vite-assets.blade.php` (new)
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/platform.blade.php`
- `resources/views/layouts/guest.blade.php`
- `resources/views/layouts/print.blade.php`
- `resources/js/app.js`
- `resources/js/spa-navigation.js`
- `resources/js/header-notifications.js`
- `public/build/*` (after `npm run build` — CSS `app-ysE60bq2.css`, JS `app-CWpt7aMH.js`)

---

## 8. Screenshots / previews

Screenshots are environment-specific. After `npm run build` and `php artisan view:clear`, verify locally:

1. Auth (guest layout) — centered card, Cairo headings  
2. Clinic dashboard — `.dash-kpi`, `.dash-panel`, grid `xl:grid-cols-12`  
3. SPA transition — no layout jump, sidebar fixed  

---

## Commands

```bash
npm run build
php artisan view:clear
php artisan test --filter=DashboardTest
```
