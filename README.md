# Memorial Website

Single-source agentic project driven by `PRIMORDIAL.md`. Start with Stage 1 (Setup) to scaffold a Laravel 11 app configured for SQLite with Tailwind + Preline, and a base layout ready for HTMX/Alpine/GLightbox.

Quick start
- Read `PRIMORDIAL.md` to understand the workflow and checkpoints.
- Follow `docs/SETUP.md` to run Stage 1 bootstrap (Windows or macOS/Linux).
- Track progress in `STATUS.md` and use the commit tags from `AGENTS.md`.

Key files
- `PRIMORDIAL.md` — Genesis doc: roles, stages, templates, and machine-readable blocks.
- `AGENTS.md` — Roles, handoffs, working agreements.
- `STATUS.md` — Task tracking table (todo → in_progress → done).
- `PROMPT_LIBRARY.md` — Canonical prompts for each agent.
- `docs/SETUP.md` — Stage 1 instructions + scripts.

Dev servers (after Stage 1)
- Terminal A: `php artisan serve`
- Terminal B: `npm run dev`

Checkpoints
- Stage completion messages must include exact tags, e.g., `checkpoint: project setup complete`.

## Visual UI Checks (Playwright + MCP)

Purpose: quick, deterministic desktop/mobile screenshots for key pages to catch regressions early. One browser (Chromium), two device profiles.

### One‑time setup
- Install Node deps: `npm install`
- Install Playwright browsers: `npm run ui:install`

### Common commands
- Run all visuals (desktop + mobile): `npm run ui:check`
- Desktop only: `npm run ui:desk`
- Mobile only: `npm run ui:mobile`
- Update baselines after intentional UI changes: `npm run ui:update`

Notes
- Tests auto‑start the app in testing mode and run a fresh `migrate:fresh --seed` for determinism.
- HTML report at `playwright-report/index.html` (created on run).

### Common scenarios covered
- Public pages render: Home (`/`), Gallery (`/gallery`), Wishwall (`/wishes`), Updates (`/updates`).
- Updates show: navigate to first seeded update and capture.
- Admin views: Dashboard, Wishes moderation, Updates index (logs in with seeded admin).

### Quick ad‑hoc screenshot (no test change)
- Simplest: `npm run mcp:shot -- --url /gallery`
  - Defaults: captures BOTH desktop and mobile to `mcp-artifacts/gallery.desktop.png` and `mcp-artifacts/gallery.mobile.png`.
  - Override device: `--device desktop|mobile|both`.
  - Name prefix: `--name myshot` produces `myshot.desktop.png`/`myshot.mobile.png`.
  - Output saved under `mcp-artifacts/` (git‑ignored).

### Prompt snippet for agents
Use this to request a quick screenshot via MCP during reviews:

```
You are the Testing & CI Agent. Use the MCP Playwright server:
- goto /gallery (wait for network idle)
- screenshot both devices (defaults)
Return the saved paths and any rendering issues.
```
