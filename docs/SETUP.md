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

Run the bootstrap script (macOS/Linux)
- From the repo root and with PHP/Composer/Node installed:
  - `bash scripts/unix/setup-stage1.sh`
- Same outputs as Windows flow; use Homebrew or your distro package manager to install prerequisites.

Dev servers
- Terminal A: `cd app && php artisan serve`
- Terminal B: `cd app && npm run dev`

Commit conventions
- While working on a task: `git commit -m "task: SETUP-01 message"`
- On stage completion: `git commit -m "checkpoint: project setup complete"`

Troubleshooting
- If a command is not found after winget installs, restart PowerShell.
- If `spatie/image-optimizer` warns about missing binaries, that’s OK for now; they’re used in later stages (you can add jpegoptim/pngquant/etc. later on the server).
