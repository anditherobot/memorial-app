# Agent Playbook — Visual UI Checks (Playwright + MCP)

Objective
- Provide a dead‑simple runbook any agent can follow to run visual checks, capture screenshots, and triage differences.

Scope
- Browser: Chromium only.
- Devices: desktop (1280×800) and mobile (390×844).
- Pages: Home, Gallery, Wishwall, Updates (index + one show), Admin (dashboard, wishes, updates).

Prerequisites
- PHP 8.3 + Composer; Node 18+.
- App installs and seeds cleanly: `composer install`, `npm ci`, `php artisan migrate:fresh --seed`.

Core Commands (will be added in TEST-11)
- Install browsers: `npm run ui:install`
- Run all visuals (desktop+mobile): `npm run ui:check`
- Desktop only: `npm run ui:desk`
- Mobile only: `npm run ui:mobile`
- Update baselines intentionally: `npm run ui:update`
- MCP server (ad-hoc shots): `npm run mcp:dev`

Files & Outputs
- Config: `playwright.config.ts`
- Tests: `tests/e2e/smoke.spec.ts`
- Baselines: `tests/e2e/__screenshots__/`
- Report: `playwright-report/` (HTML)
- MCP artifacts: `mcp-artifacts/` (git‑ignored)

Standard Procedure
1) Ensure test env
   - `php artisan migrate:fresh --seed --env=testing`
2) Run suite
   - `npm run ui:check`
3) Review outcome
   - If green: done.
   - If failing visuals: open `playwright-report/index.html`, inspect diffs.
4) Decide
   - Regression: fix UI and re‑run step 2.
   - Intended change: run `npm run ui:update` and commit baselines.

Ad-hoc Screenshots (MCP)
1) Quick CLI capture: `npm run mcp:shot -- --url /gallery`
   - Defaults to BOTH devices → `mcp-artifacts/gallery.desktop.png` and `.mobile.png`.
   - Override with `--device desktop|mobile|both`, rename with `--name myshot`.
2) For interactive flows, start MCP server: `npm run mcp:dev` and issue `goto` then `screenshot` tool calls.
3) Check output under `mcp-artifacts/`.

Pass/Fail Criteria
- All visual tests pass with zero unexpected diffs.
- Report is clean; seeded content renders; admin pages accessible with seeded admin user.

Triage Tips
- Flaky diffs: ensure reduced motion; wait for `networkidle`; verify seeded dates/content.
- Fonts/icons: confirm assets are local and loaded before screenshot.

Commit & Tags
- Use `task: TEST-11` for related commits.
- Stage completion tag unchanged: `checkpoint: testing complete` when adopted in CI.
