# STATUS.md

Memorial Website — Task Tracking

Legend: `todo`, `in_progress`, `done`, `blocked`

| Stage | Task ID | Status | Commit | Notes |
|-------|---------|--------|--------|-------|
| Setup | SETUP-01 | done | task: SETUP-01 | Init Laravel 11 project |
| Setup | SETUP-02 | done | task: SETUP-02 | Configure .env for SQLite; create database.sqlite |
| Setup | SETUP-03 | done | task: SETUP-03 | Install Tailwind + Preline |
| Setup | SETUP-04 | done | task: SETUP-04 | Install intervention/image, spatie/image-optimizer |
| Setup | SETUP-05 | done | task: SETUP-05 | Base layout with HTMX, Alpine, GLightbox CDNs |
| Setup | SETUP-06 | done | task: SETUP-06 | Git init + .gitignore |
| Data | DATA-01 | todo |  | media + media_derivatives |
| Data | DATA-02 | todo |  | wishes + attachments |
| Data | DATA-03 | todo |  | posts + attachments |
| Data | DATA-04 | todo |  | SQLite PRAGMA migration (WAL + FK) |
| Backend | BE-01 | todo |  | UploadController (image/video) |
| Backend | BE-02 | todo |  | Jobs: ProcessImage, GeneratePoster |
| Backend | BE-03 | todo |  | WishController (captcha, spam controls) |
| Backend | BE-04 | todo |  | PostController (auth posts, pagination) |
| Backend | BE-05 | todo |  | Moderation routes (/admin) |
| Backend | BE-06 | todo |  | Rate limiting |
| Frontend | FE-01 | todo |  | Layout (header, footer) |
| Frontend | FE-02 | todo |  | Home + Bio |
| Frontend | FE-03 | todo |  | Gallery (GLightbox) |
| Frontend | FE-04 | todo |  | Wishwall (htmx form + list) |
| Frontend | FE-05 | todo |  | Updates (htmx load more) |
| Frontend | FE-06 | todo |  | Responsive + <20 KB CSS |
| Testing & CI | TEST-01 | todo |  | Feature test Wishwall (captcha mocked) |
| Testing & CI | TEST-02 | todo |  | Feature test Updates |
| Testing & CI | TEST-03 | todo |  | Unit test ProcessImage |
| Testing & CI | TEST-04 | todo |  | Browser test (optional) |
| Testing & CI | TEST-05 | todo |  | GitHub Actions (pint, phpunit, npm build) |
| Deployment & Ops | OPS-01 | todo |  | Server prep doc |
| Deployment & Ops | OPS-02 | todo |  | Nginx vhost config |
| Deployment & Ops | OPS-03 | todo |  | Queue worker systemd |
| Deployment & Ops | OPS-04 | todo |  | Backup script (sqlite3 .backup + rclone) |
| Deployment & Ops | OPS-05 | todo |  | Restore doc |
| Deployment & Ops | OPS-06 | todo |  | Cron jobs (scheduler, nightly backups) |

Checkpoint tags (use exactly):
- setup: `checkpoint: project setup complete`
- data: `checkpoint: data model complete`
- backend: `checkpoint: backend complete`
- frontend: `checkpoint: frontend complete`
- testing: `checkpoint: testing complete`
- ops: `checkpoint: deployment complete`
