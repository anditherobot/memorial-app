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
| Data | DATA-01 | in_progress |  | media + media_derivatives |
| Data | DATA-02 | todo |  | wishes + attachments |
| Data | DATA-03 | todo |  | posts + attachments |
| Data | DATA-04 | todo |  | SQLite PRAGMA migration (WAL + FK) |
| Backend | BE-01 | done |  | UploadController (image/video) |
| Backend | BE-02 | done |  | Jobs: ProcessImage, GeneratePoster |
| Backend | BE-03 | done |  | WishController (captcha, spam controls) |
| Backend | BE-04 | done |  | PostController (auth posts, pagination) |
| Backend | BE-05 | done |  | Moderation routes (/admin) + login |
| Backend | BE-06 | done |  | Rate limiting |
| Frontend | FE-01 | done |  | Layout (header, footer) |
| Frontend | FE-02 | done |  | Home + Bio |
| Frontend | FE-03 | done |  | Gallery (GLightbox) |
| Frontend | FE-04 | done |  | Wishwall (htmx form + list) |
| Frontend | FE-05 | done |  | Updates (htmx load more) |
| Frontend | FE-06 | done |  | Responsive + <20 KB CSS |
| Testing & CI | TEST-01 | done | task: TEST-01 | Feature test Wishwall (captcha mocked) |
| Testing & CI | TEST-02 | done | task: TEST-02 | Feature test Updates |
| Testing & CI | TEST-03 | done | task: TEST-03 | Unit test ProcessImage |
| Testing & CI | TEST-04 | done | task: TEST-04 | Browser test (optional) |
| Testing & CI | TEST-05 | done | task: TEST-05 | GitHub Actions (pint, phpunit, npm build) |
| Testing & CI | TEST-06 | done | task: TEST-06 | Feature tests: Gallery (samples, pagination, thumbnails) |
| Testing & CI | TEST-07 | done | task: TEST-07 | Feature tests: Admin updates management (create/edit/delete) |
| Testing & CI | TEST-08 | done | task: TEST-08 | Feature tests: Upload API (happy path + max file) |
| Testing & CI | TEST-09 | done | task: TEST-09 | Unit tests: Wish model, Post model, Jobs (ProcessImage/GeneratePoster) |
| Testing & CI | TEST-10 | done | task: TEST-10 | Test DB hardening: SQLite :memory: handling + config fix |
| Deployment & Ops | OPS-01 | done | task: OPS-01 | Server prep doc |
| Deployment & Ops | OPS-02 | done | task: OPS-02 | Nginx vhost config |
| Deployment & Ops | OPS-03 | done | task: OPS-03 | Queue worker systemd |
| Deployment & Ops | OPS-04 | done | task: OPS-04 | Backup script (sqlite3 .backup + rclone) |
| Deployment & Ops | OPS-05 | done | task: OPS-05 | Restore doc |
| Deployment & Ops | OPS-06 | done | task: OPS-06 | Cron jobs (scheduler, nightly backups) |

| Maintenance | REPO-01 | done | task: REPO-01 | Consolidate Laravel app under `app/`; moved images + sessions migration; removed duplicate root `routes/` and `resources/` |

Checkpoint tags (use exactly):
- setup: `checkpoint: project setup complete`
- data: `checkpoint: data model complete`
- backend: `checkpoint: backend complete`
- frontend: `checkpoint: frontend complete`
- testing: `checkpoint: testing complete`
- ops: `checkpoint: deployment complete`
