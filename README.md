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
- Terminal A: `cd app && php artisan serve`
- Terminal B: `cd app && npm run dev`

Checkpoints
- Stage completion messages must include exact tags, e.g., `checkpoint: project setup complete`.

