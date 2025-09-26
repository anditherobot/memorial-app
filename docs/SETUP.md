# Stage 1 — Setup Agent (Windows-first)

This guide installs prerequisites, scaffolds Laravel 11 into `app/`, configures SQLite, and wires Tailwind + Preline.

If you already have PHP, Composer, Node, and Git, skip to “Run the bootstrap script”.

Prerequisites (Windows via winget)
- Open an elevated PowerShell and run:
  - `winget install Git.Git -e`
  - `winget install PHP.PHP -e`
  - `winget install Composer.Composer -e`
  - `winget install OpenJS.NodeJS.LTS -e`

Run the bootstrap script (Windows)
- From the repo root:
  - `powershell -ExecutionPolicy Bypass -File scripts/windows/setup-stage1.ps1`
- This will:
  - Create a Laravel 11 app in `app/`
  - Configure `.env` for SQLite and create `database/database.sqlite`
  - Install Tailwind + Preline and build assets
  - Add a base Blade layout with HTMX, Alpine, GLightbox CDNs
  - Install `intervention/image` and `spatie/image-optimizer`
  - NOTE: Set `ADMIN_TOKEN` in `app/.env` to enable admin routes
  - Link public storage: `cd app && php artisan storage:link`

Run the bootstrap script (macOS/Linux)
- From the repo root and with PHP/Composer/Node installed:
  - `bash scripts/unix/setup-stage1.sh`
- Same outputs as Windows flow; use Homebrew or your distro package manager to install prerequisites.
 - Link public storage: `cd app && php artisan storage:link`

Dev servers
- Terminal A: `cd app && php artisan serve`
- Terminal B: `cd app && npm run dev`

Commit conventions
- While working on a task: `git commit -m "task: SETUP-01 message"`
- On stage completion: `git commit -m "checkpoint: project setup complete"`

Troubleshooting
- If a command is not found after winget installs, restart PowerShell.
- If `spatie/image-optimizer` warns about missing binaries, that’s OK for now; they’re used in later stages (you can add jpegoptim/pngquant/etc. later on the server).
- For admin moderation views, set an env var in `app/.env` like `ADMIN_TOKEN=your-long-secret` and pass it via `X-Admin-Token` header or `?token=...` in the URL.
- To seed an admin login account, set in `app/.env` before `php artisan migrate --seed`:
  - `ADMIN_EMAIL=admin@example.com`
  - `ADMIN_PASSWORD=your-strong-password`
  The seeder marks this user as `is_admin=1`. Admin routes require both login and admin flag.
 - SQLite “database is locked” during requests: prefer file sessions in dev. In `app/.env` set `SESSION_DRIVER=file`, then `php artisan config:clear`. We also set `PRAGMA busy_timeout=5000` at boot to reduce contention.

Frontend quality goals
- Build for production: `cd app && npm run build`. The generated CSS in `public/build/assets/*.css` should be under 20 KB thanks to Tailwind’s content-based tree-shaking.
- Run Lighthouse in Chrome on `/` and `/wishes` and `/updates`; aim for ≥ 90 in all categories. If needed, minimize images and disable heavy fonts.
