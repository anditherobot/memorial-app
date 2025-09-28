# Context Package — Visual E2E with Playwright + MCP

Purpose: define the task, workflow, and concrete build plan to introduce Playwright end‑to‑end tests with deterministic screenshots, and an MCP server for ad‑hoc UI capture and tours. This supports fast UI review and early detection/fix of regressions during the Testing & CI stage.

## Scope
- Repository: entire app (public + admin). Non‑invasive; no route changes.
- Environments: local dev, CI (GitHub Actions). Optional use on staging.
- Browsers/devices: Chromium desktop and mobile profiles initially.

## Outcomes
- Deterministic screenshots for critical pages and UI states.
- Easy ad‑hoc screenshots via MCP (agent/tooling) without writing tests.
- CI artifacts on failures for visual review; reproducible baselines.

## Deliverables
- Playwright setup: config, projects, webServer, helpers.
- Visual smoke tests under `tests/e2e/` with baseline screenshots in `tests/e2e/__screenshots__/`.
- MCP server exposing tools: `goto`, `screenshot`, `tour` for scripted captures.
- NPM scripts for install, run, update baselines, and MCP usage.
- CI workflow to run e2e, publish artifacts, and gate on visual diffs.
- Docs updates in `docs/SETUP.md` for usage and conventions.

## Constraints & Principles
- Determinism: seeded database, fixed dates where needed, animations disabled, wait for network idle.
- Lean baselines: only critical pages/states to keep repo size reasonable.
- Security: admin routes tested with seeded admin; do not weaken policies.
- Performance: keep CI runtime modest; parallelize across browsers.

## Target Pages & States (initial)
- Public: Home (`/`), Gallery (`/gallery`), Wishwall (`/wishes` pre‑submit), Updates index (`/updates`), Updates show (first seeded).
- Admin: Dashboard (`/admin`), Wishes moderation (`/admin/wishes`), Updates index (`/admin/updates`).
- Viewports: desktop 1280×800 and mobile 390×844.

## Implementation Plan
1) Dependencies
   - Dev: `@playwright/test` (and `typescript` if using TS tests).
   - Script: `npx playwright install` during setup/CI.

2) Configuration
   - `playwright.config.ts`
     - `use.baseURL` from `APP_URL` (fallback `http://127.0.0.1:8000`).
     - `projects`: `chromium-desktop`, `chromium-mobile`.
     - `use.screenshot = 'only-on-failure'` for general tests; explicit captures for visuals.
     - `use.colorScheme = 'light'`, `reduceMotion = 'reduce'`.
     - `snapshotDir: tests/e2e/__screenshots__`.
     - `webServer`: `php artisan serve --env=testing` with `timeout` and `reuseExistingServer`.

3) Test Env & Data
   - `.env.testing` with SQLite database file and `APP_URL`.
   - Global setup (or per‑suite hook):
     - `php artisan migrate:fresh --seed --env=testing` before tests.
   - Auth helper to login admin (form POST or cookie injection) for admin routes.

4) Visual Smoke Tests
   - `tests/e2e/smoke.spec.ts`
     - Navigate to each page/state, wait for content stable, take full‑page screenshots with consistent names (e.g., `home.desktop.png`, `gallery.mobile.png`).

5) MCP Server
   - File: `mcp/playwright-server.ts`
     - Tools:
       - `goto(url)` — navigate to route, wait for `networkidle`.
       - `screenshot(path?, options?)` — capture current page; defaults to full page, current device.
       - `tour(name, steps[])` — run scripted steps (click, fill, goto, wait) and emit a gallery of screenshots.
     - Options: `device` (desktop/mobile), `viewport`, auth cookie injection for admin.
     - Output: `mcp-artifacts/` (git‑ignored).

6) NPM Scripts
   - `shots:install` → `playwright install`.
   - `test:e2e` → run Playwright tests (with webServer).
   - `test:e2e:ui` → open Playwright UI viewer.
   - `shots:update` → `PLAYWRIGHT_UPDATE_SNAPSHOTS=1` run to refresh baselines.
   - `serve:test` → `php artisan serve --env=testing` (for manual MCP runs).
   - `mcp:dev` → start MCP server.

7) CI Integration (GitHub Actions)
   - Jobs:
     - Install PHP/Composer + Node deps; build assets.
     - `php artisan migrate:fresh --seed --env=testing`.
     - `npx playwright install --with-deps`.
     - Run Playwright tests.
     - Upload artifacts: `playwright-report/`, screenshot diffs.
   - Manual `workflow_dispatch` to update baselines when approved.

## Success Criteria (Definition of Done)
- Local: `npm run test:e2e` passes; screenshots created/updated deterministically; MCP `goto` and `screenshot` produce artifacts.
- CI: green job with artifacts on failure; visual diffs gate enforced; docs updated.
- Repo hygiene: baselines committed for critical screens; `mcp-artifacts/` and reports ignored by Git.

## Risks & Mitigations
- Flaky visuals (fonts, timing): embed system fonts; use `waitForLoadState('networkidle')`; disable animations; seed fixed content.
- Large baselines: restrict to critical screens; prune regularly.
- Admin auth drift: centralize login helper; verify seed user presence.

## Handoffs & Tags
- Primary role: Testing & CI Agent.
- Commit tags: `task: TEST-11` for implementation commits.
- Stage completion tag (unchanged): `checkpoint: testing complete`.

## Quick Usage (once implemented)
- Install browsers: `npm run shots:install`.
- Run e2e visuals: `npm run test:e2e`.
- Update baselines: `npm run shots:update`.
- MCP ad‑hoc: `npm run mcp:dev` then request `screenshot /gallery`.

---
This plan is intentionally concise and actionable. Follow `STATUS.md` to track progress and update state transitions (`todo → in_progress → done`).

