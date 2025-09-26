# PRIMORDIAL.md

Memorial Website — Agentic Workflow & Project Pack

This file is the single source of truth. It defines workflow, agent roles, tasks, prompt templates, and execution order. The Onboarding Agent consumes this document to generate secondary docs, initialize the repo, and stage the rest of the agents.

---

## Quick Start

1) Launch the Onboarding Agent with this file in the repo root.
2) It generates `AGENTS.md`, `STATUS.md`, and `PROMPT_LIBRARY.md`, and performs the initial repo bootstrap.
3) Work proceeds stage-by-stage with checkpoint commits after each stage.

---

## Front Matter Manifest

```yaml
project:
  name: Memorial Website
  default_branch: main
  description: Public memorial site with gallery, wishwall, and updates.
stack:
  runtime: [PHP 8.3, Laravel 11]
  db: [SQLite]
  frontend: [Blade, Tailwind CSS, Preline, HTMX, Alpine.js, GLightbox]
  tooling: [Pest, GitHub Actions, laravel/pint]
infra:
  os: Ubuntu 22.04 LTS
  web: Nginx
  process: systemd queue workers
  backups: sqlite3 .backup + rclone
  ssl: Let’s Encrypt
constraints:
  css_budget_kb: 20
  lighthouse_min_score: 90
security:
  captcha: enabled for wishwall
  rate_limits: conservative defaults on uploads and forms
  moderation: admin routes + policies
conventions:
  commits:
    checkpoint_tag: "checkpoint: <stage> complete"
    task_tag: "task: <ID>"
  coding:
    style: compact, readable, no one-letter vars
```

---

## Part 0: Workflow Overview

We follow the Agents.compact methodology:

- Staged execution with clear inputs/outputs and a Definition of Done.
- Each stage ends with a checkpoint commit.
- Execution order: Onboarding → Setup → Data Modeling → Backend → Frontend → Testing & CI → Deployment & Ops.

---

## Part 1: Core Repository Documents (to generate)

When PRIMORDIAL.md is ingested, the Onboarding Agent creates:

1) `AGENTS.md`
   - Defines agent roles, responsibilities, and handoff criteria.
2) `STATUS.md`
   - Task tracking table with columns: Stage | Task ID | Status | Commit | Notes
3) `PROMPT_LIBRARY.md`
   - Canonical prompt templates for codegen tasks with inputs/outputs.

---

## Part 2: Agent Roles

- Onboarding Agent → initializes repo, creates core docs, aligns with PRIMORDIAL.
- Setup Agent → environment config, Laravel + SQLite init, tooling.
- Data Modeling Agent → migrations, models, factories, seeds.
- Backend Agent → controllers, jobs, validation, policies, moderation, rate limiting.
- Frontend Agent → Blade, Tailwind, HTMX, Alpine, GLightbox, responsive budget.
- Testing & CI Agent → tests, CI workflows, quality gates.
- Deployment & Ops Agent → server prep, Nginx, queue workers, backups, restore docs, cron.

---

## Part 3: Prompt Library (inline definitions)

Use these identifiers in `PROMPT_LIBRARY.md`. Onboarding Agent should copy and expand them with parameters.

```prompt
ID: TEMPLATE-SETUP-001
Title: Laravel + SQLite init
Inputs: project_name, php_version, laravel_version
Output: New Laravel app, SQLite configured, Tailwind + Preline installed
Acceptance: App boots locally, Tailwind builds, .env set for SQLite
```

```prompt
ID: TEMPLATE-DATA-001
Title: Model + migration + factory
Inputs: name, fields[], relations[], indexes[], policies?
Output: Migration, Eloquent model, factory, seed stub
Acceptance: php artisan migrate:fresh --seed succeeds
```

```prompt
ID: TEMPLATE-BE-001
Title: CRUD controller + Form Requests
Inputs: model, routes, validation_rules
Output: Controller, FormRequests, routes, policies (if needed)
Acceptance: Feature tests pass, requests validated, policies enforced
```

```prompt
ID: TEMPLATE-BE-002
Title: Queue job (image/video)
Inputs: source_path, operations[], outputs[]
Output: Job class, pipeline, storage paths, events
Acceptance: Jobs process asynchronously, idempotent, errors handled
```

```prompt
ID: TEMPLATE-FE-001
Title: Blade view (Tailwind/Preline)
Inputs: layout_slot, components, htmx_interactions
Output: Blade templates, partials, assets hooks
Acceptance: Lighthouse ≥ 90, CSS budget ≤ 20 KB
```

```prompt
ID: TEMPLATE-TEST-001
Title: Pest feature test
Inputs: route, scenario, assertions
Output: Pest test file with arrange/act/assert and factories
Acceptance: Test runs green locally and in CI
```

---

## Part 4: Stages & Tasks

### Stage 1: Setup Agent

Tasks
- SETUP-01: Init Laravel 11 project
- SETUP-02: Configure `.env` for SQLite, create `database.sqlite`
- SETUP-03: Install Tailwind + Preline
- SETUP-04: Install `intervention/image`, `spatie/image-optimizer`
- SETUP-05: Base layout with HTMX, Alpine, GLightbox CDNs
- SETUP-06: Git init + `.gitignore`

Done
- Project boots locally (`php artisan serve`)
- Tailwind compiles (`npm run dev`), Preline wired

Checkpoint
- Commit message contains: `checkpoint: project setup complete`

---

### Stage 2: Data Modeling Agent

Tasks
- DATA-01: `media` + `media_derivatives`
- DATA-02: `wishes` + attachments
- DATA-03: `posts` + attachments
- DATA-04: SQLite PRAGMA migration (WAL + FK)

Done
- `php artisan migrate:fresh --seed` succeeds

Checkpoint
- Commit message contains: `checkpoint: data model complete`

---

### Stage 3: Backend Agent

Tasks
- BE-01: UploadController (image/video)
- BE-02: Jobs: `ProcessImage`, `GeneratePoster`
- BE-03: WishController (captcha, spam controls)
- BE-04: PostController (auth posts, pagination)
- BE-05: Moderation routes (`/admin`)
- BE-06: Rate limiting

Done
- Uploads processed, Wishwall + Updates work securely

Checkpoint
- Commit message contains: `checkpoint: backend complete`

---

### Stage 4: Frontend Agent

Tasks
- FE-01: Layout (header, footer)
- FE-02: Home + Bio
- FE-03: Gallery (GLightbox)
- FE-04: Wishwall (htmx form + list)
- FE-05: Updates (htmx load more)
- FE-06: Responsive + <20 KB CSS

Done
- Lighthouse ≥ 90 (Performance/Accessibility/Best Practices/SEO)

Checkpoint
- Commit message contains: `checkpoint: frontend complete`

---

### Stage 5: Testing & CI Agent

Tasks
- TEST-01: Feature test Wishwall (captcha mocked)
- TEST-02: Feature test Updates
- TEST-03: Unit test `ProcessImage`
- TEST-04: Browser test (optional)
- TEST-05: GitHub Actions (pint, phpunit, npm build)

Done
- Green suite locally and in CI; Pint clean

Checkpoint
- Commit message contains: `checkpoint: testing complete`

---

### Stage 6: Deployment & Ops Agent

Tasks
- OPS-01: Server prep doc
- OPS-02: Nginx vhost config
- OPS-03: Queue worker systemd
- OPS-04: Backup script (`sqlite3 .backup` + rclone)
- OPS-05: Restore doc
- OPS-06: Cron jobs (scheduler, nightly backups)

Done
- Live over HTTPS; backups tested and restorable

Checkpoint
- Commit message contains: `checkpoint: deployment complete`

---

## Part 5: Execution Order

1) Launch Onboarding Agent with PRIMORDIAL.md.
2) Generate AGENTS.md, STATUS.md, PROMPT_LIBRARY.md; complete repo init.
3) After Setup checkpoint, proceed to Data Modeling + Backend.
4) Then Frontend.
5) Finish with Testing & CI, then Deployment & Ops.

---

## Machine-Readable Blocks (for agents)

```yaml
agents:
  - id: onboarding
    name: Onboarding Agent
    outputs:
      - AGENTS.md
      - STATUS.md
      - PROMPT_LIBRARY.md
    done_when:
      - files_created: [AGENTS.md, STATUS.md, PROMPT_LIBRARY.md]
      - repo_initialized: true
  - id: setup
    name: Setup Agent
    tasks: [SETUP-01, SETUP-02, SETUP-03, SETUP-04, SETUP-05, SETUP-06]
  - id: data
    name: Data Modeling Agent
    tasks: [DATA-01, DATA-02, DATA-03, DATA-04]
  - id: backend
    name: Backend Agent
    tasks: [BE-01, BE-02, BE-03, BE-04, BE-05, BE-06]
  - id: frontend
    name: Frontend Agent
    tasks: [FE-01, FE-02, FE-03, FE-04, FE-05, FE-06]
  - id: testing
    name: Testing & CI Agent
    tasks: [TEST-01, TEST-02, TEST-03, TEST-04, TEST-05]
  - id: ops
    name: Deployment & Ops Agent
    tasks: [OPS-01, OPS-02, OPS-03, OPS-04, OPS-05, OPS-06]
```

```yaml
stages:
  - id: setup
    checkpoint: "checkpoint: project setup complete"
    tasks:
      - SETUP-01
      - SETUP-02
      - SETUP-03
      - SETUP-04
      - SETUP-05
      - SETUP-06
  - id: data
    checkpoint: "checkpoint: data model complete"
    tasks: [DATA-01, DATA-02, DATA-03, DATA-04]
  - id: backend
    checkpoint: "checkpoint: backend complete"
    tasks: [BE-01, BE-02, BE-03, BE-04, BE-05, BE-06]
  - id: frontend
    checkpoint: "checkpoint: frontend complete"
    tasks: [FE-01, FE-02, FE-03, FE-04, FE-05, FE-06]
  - id: testing
    checkpoint: "checkpoint: testing complete"
    tasks: [TEST-01, TEST-02, TEST-03, TEST-04, TEST-05]
  - id: ops
    checkpoint: "checkpoint: deployment complete"
    tasks: [OPS-01, OPS-02, OPS-03, OPS-04, OPS-05, OPS-06]
```

```yaml
templates:
  - id: TEMPLATE-SETUP-001
  - id: TEMPLATE-DATA-001
  - id: TEMPLATE-BE-001
  - id: TEMPLATE-BE-002
  - id: TEMPLATE-FE-001
  - id: TEMPLATE-TEST-001
```

---

## Handoff Notes for Onboarding Agent

- Create `AGENTS.md`, `STATUS.md`, `PROMPT_LIBRARY.md` using the roles, stages, and templates above.
- Initialize git, create `.gitignore` (Laravel + Node), and set default branch to `main`.
- Seed `STATUS.md` with all Task IDs in “todo” state.
- Add a short `README.md` pointing to this PRIMORDIAL.md (optional if already present).

---

## Commit Message Conventions

- Use `task: <ID>` in commits addressing a specific task.
- Use the stage-specific `checkpoint:` message upon completing each stage, exactly as defined in stages.

