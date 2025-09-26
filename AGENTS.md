# AGENTS.md

Memorial Website — Agent Roles, Handoffs, and Working Agreements

Scope: Applies to the entire repository. Agents must follow these roles, handoff criteria, and conventions. This file derives from `PRIMORDIAL.md` and stays authoritative for day‑to‑day work.

---

## Execution Order

Onboarding → Setup → Data Modeling → Backend → Frontend → Testing & CI → Deployment & Ops

Each stage ends with a checkpoint commit using the exact tag from `PRIMORDIAL.md`.

---

## Roles & Handoffs

### Onboarding Agent
- Responsibilities: Generate `AGENTS.md`, `STATUS.md`, `PROMPT_LIBRARY.md`; align repo with `PRIMORDIAL.md`.
- Inputs: `PRIMORDIAL.md` Front Matter, Stages, Templates.
- Outputs: Core docs created; repo optionally initialized.
- Definition of Done: Core docs exist and reflect current plan.
- Handoff: Signal Setup Agent to begin.

### Setup Agent
- Responsibilities: Laravel 11 init, SQLite config, Tailwind + Preline, base layout, developer tooling.
- Inputs: `PRIMORDIAL.md`, templates `TEMPLATE-SETUP-001`, `TEMPLATE-FE-001` (layout stub).
- Outputs: Fresh Laravel app; `.env` for SQLite; `database/database.sqlite`; Tailwind build works.
- Definition of Done: App boots; Tailwind compiles; Preline wired.
- Handoff: Commit with `checkpoint: project setup complete`.

### Data Modeling Agent
- Responsibilities: Migrations, models, factories, seeds; SQLite PRAGMAs for WAL + FKs.
- Inputs: `TEMPLATE-DATA-001` specs for entities.
- Outputs: `media`, `media_derivatives`, `wishes`, `posts` (+ attachments), PRAGMA migration.
- Definition of Done: `php artisan migrate:fresh --seed` succeeds.
- Handoff: Commit with `checkpoint: data model complete`.

### Backend Agent
- Responsibilities: Controllers, jobs, validation, policies, moderation routes, rate limiting.
- Inputs: Data models; `TEMPLATE-BE-001`, `TEMPLATE-BE-002`.
- Outputs: Upload pipeline, Wishwall endpoints (captcha, spam controls), Posts (auth + pagination), Admin moderation.
- Definition of Done: Uploads processed; Wishwall & Updates function securely.
- Handoff: Commit with `checkpoint: backend complete`.

### Frontend Agent
- Responsibilities: Blade views, components, HTMX interactions, Alpine helpers, GLightbox gallery, responsive.
- Inputs: Backend routes; `TEMPLATE-FE-001`.
- Outputs: Layout, Home/Bio, Gallery, Wishwall, Updates; CSS ≤ 20 KB.
- Definition of Done: Lighthouse ≥ 90 across categories.
- Handoff: Commit with `checkpoint: frontend complete`.

### Testing & CI Agent
- Responsibilities: Pest tests (feature/unit), optional browser tests; GitHub Actions for pint, phpunit, npm build.
- Inputs: App code; `TEMPLATE-TEST-001`.
- Outputs: Green suite locally and in CI; quality gates enforced.
- Definition of Done: All tests pass; CI green.
- Handoff: Commit with `checkpoint: testing complete`.

### Deployment & Ops Agent
- Responsibilities: Server prep doc, Nginx vhost, systemd queue workers, backups (sqlite3 .backup + rclone), restore, cron.
- Inputs: Built app; infra constraints in Front Matter.
- Outputs: Live site over HTTPS; verified backups and restores.
- Definition of Done: Site live; backups tested.
- Handoff: Commit with `checkpoint: deployment complete`.

---

## Working Agreements

- Commit tags
  - Use `task: <ID>` for task‑focused commits.
  - Use the exact `checkpoint:` tag on stage completion.
- Code style
  - Compact, readable, descriptive names; avoid one‑letter vars.
  - Keep changes minimal and scoped to the task.
- Security
  - Enable captcha on Wishwall; apply conservative rate limits.
  - Enforce policies for admin/moderation endpoints.
- Performance
  - CSS budget ≤ 20 KB; Lighthouse ≥ 90.
- Documentation
  - Update `STATUS.md` on each task start/finish.
  - Keep `PROMPT_LIBRARY.md` in sync when adding new patterns.

---

## Task Status Lifecycle

`todo` → `in_progress` → `done` (or `blocked` with a brief note and dependency).

Agents must update the task row in `STATUS.md` when state changes.

