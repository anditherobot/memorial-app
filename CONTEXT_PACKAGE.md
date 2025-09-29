# Root Context Package — Memorial Website

Purpose: a concise, practical bundle of repo context any engineer or agent can load in seconds to understand, run, and work on the project.

## TL;DR
- Stack: Laravel 11 + SQLite + TailwindCSS + Vite + Preline + HTMX/Alpine (via CDN).
- Admin URL: `/admin` — default credentials from env or seeders (dev): `admin@example.com` / `secret`.
- Primary goals (current): memorial-focused Admin UI, gallery/uploads, wish moderation, updates/posts, and simple memorial content/events.

## Run Locally
1) PHP + Composer + Node 18+
2) Install deps: `composer install` and `npm ci` (or `npm i`)
3) Ensure SQLite DB file exists (root-level `.env` already points to `dev.sqlite`):
   - `touch database/dev.sqlite`
4) Migrate and seed: `php artisan migrate:fresh --seed`
5) Start dev:
   - Terminal A: `php artisan serve`
   - Terminal B: `npm run dev`

## Where Things Live
- Layouts
  - Admin shell and sidebar: `resources/views/layouts/admin.blade.php`
  - Admin sidebar items: `resources/views/admin/_navigation.blade.php`
- Admin pages
  - Dashboard: `resources/views/admin/dashboard.blade.php`
  - Gallery: `resources/views/admin/gallery.blade.php`
  - Wishes moderation: `resources/views/wishes/admin.blade.php`
  - Updates CRUD: `resources/views/updates/admin/*.blade.php`
  - Tasks (internal tracker): `resources/views/admin/tasks/index.blade.php`
  - Docs: `resources/views/admin/documentation.blade.php`
- Public pages
  - Home: `resources/views/home.blade.php` (controller: `app/Http/Controllers/HomeController.php`)
  - Gallery: `resources/views/gallery/index.blade.php` (via `GalleryController`)
  - Wishwall: `resources/views/wishes/index.blade.php` (via `WishController`)
- Assets and build
  - Tailwind config: `tailwind.config.js`
  - CSS entry: `resources/css/app.css`
  - JS entry (bundles Preline): `resources/js/app.js`
  - Vite config: `vite.config.js`
- Routing
  - All HTTP routes: `routes/web.php`

## Key Endpoints (selected)
- Public
  - `GET /` home
  - `GET /gallery`
  - `GET /wishes`, `POST /wishes` (rate-limited; honeypot spam trap)
  - `GET /upload`, `POST /upload` (rate-limited)
- Admin (session auth + `is_admin`)
  - `GET /admin` dashboard
  - `GET /admin/wishes` + approve/delete
  - `GET /admin/gallery` + upload/delete
  - `GET /admin/updates` (+ create/edit/delete)
  - `GET /admin/tasks` CRUD
  - `GET /admin/docs`
- Admin token (optional alt moderation)
  - `GET /admin-token/wishes` etc. using `X-Admin-Token` or `?token=`

## Data Model (current)
- Media: originals + derivatives for thumbs/posters (image/video)
- Wishes: guest messages with moderation flags
- Posts: updates/announcements with attachments
- Tasks: lightweight tracker for repo tasks
- PRAGMAs: SQLite WAL + foreign keys migration
- In-progress (v0.8.0 scope): `memorial_events`, `memorial_content`

Migrations live in `database/migrations/` and are reversible. Seeders create a demo admin and sample content.

## Security & Limits
- Admin session guard + `AdminOnly` middleware
- Optional `AdminToken` middleware for token-based moderation
- Rate limiting: `wish-submit` and `uploads` via `RateLimiter`
- Spam protection: honeypot field on wish submission

## Admin UI Notes
- Header is fixed; sidebar sits below it and slides in on mobile.
- Edit admin shell at `resources/views/layouts/admin.blade.php` and nav at `resources/views/admin/_navigation.blade.php`.
- Lighthouse/CSS budget goals: CSS ≤ 20KB, aim ≥ 90 across categories.

## Developer Cheatsheet
- Migrate fresh + seed: `php artisan migrate:fresh --seed`
- Generate thumbnails: `php artisan thumbnails:generate`
- Queue (if switched to async): `php artisan queue:work`
- Tests: `php artisan test` (unit/feature)

## Agentic Workflow

This project follows an "agentic" development process, where tasks are broken down into stages, and each stage is handled by a specific "agent" persona. This process is defined in `AGENTS.md`.

*   **`AGENTS.md`:** Defines the roles, responsibilities, and handoff criteria for each agent in the development process.
*   **`STATUS.md`:** Tracks the status of each task in the project.
*   **`PROMPT_LIBRARY.md`:** A collection of canonical prompts for each agent.

## Conventions

- Commit tags: `task: <ID>` for task commits; `checkpoint: <stage complete>` at stage handoff.
- For more details on the agentic workflow and conventions, see `AGENTS.md`.

## Minimal Task Map (active)
- Admin UI polish (sidebar/header/spacing)
- Memorial Events CRUD (funeral/viewing/burial/repass)
- Memorial Content (bio/details/contact) → home integration

## What Not To Commit
- `storage/framework/*`, `storage/logs/*`, and uploads under `storage/app/public/*` are ignored.
- Local SQLite files under `database/*.sqlite*` are ignored.

## Quick Pointers for Agents
- Start with `AGENTS.md` for roles/handoffs; track in `STATUS.md`.
- Use small, focused patches. Keep naming descriptive and code compact.

That’s it — this is the high-signal context to get productive fast.