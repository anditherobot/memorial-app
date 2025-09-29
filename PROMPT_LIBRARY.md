# PROMPT_LIBRARY.md

Memorial Website — Canonical Prompt Templates

Usage: For each task, pick the closest template, fill inputs, and include acceptance criteria. Keep prompts concise and scoped to the current task.

Global constraints to keep in mind:
- Stack: PHP 8.3, Laravel 11, SQLite. Frontend: Blade, Tailwind, Preline, HTMX, Alpine, GLightbox.
- CSS budget ≤ 20 KB. Lighthouse ≥ 90.
- Security: captcha on Wishwall; conservative rate limits; admin policies.

---

## TEMPLATE-SETUP-001 — Laravel + SQLite init

Inputs
- `project_name`: string
- `php_version`: default 8.3
- `laravel_version`: default 11

Acceptance
- App boots locally (`php artisan serve`).
- SQLite configured; `database/database.sqlite` exists.
- Tailwind + Preline installed; `npm run dev` builds.

Prompt
```
You are the Setup Agent. Create a fresh Laravel {laravel_version} app named {project_name} for PHP {php_version} with SQLite.

Tasks:
- Install Laravel and required PHP extensions.
- Configure `.env` for SQLite, create `database/database.sqlite`.
- Install Tailwind CSS and Preline; wire scripts and build.
- Add base Blade layout with Tailwind + Preline assets included.

Deliverables:
- Fresh app directory, SQLite DB file, working Tailwind build.
- Minimal base layout view using Tailwind + Preline.

Definition of Done:
- App serves locally; Tailwind compiles without errors.
```

---

## TEMPLATE-DATA-001 — Model + migration + factory

Inputs
- `name`: Eloquent model name (PascalCase)
- `fields[]`: list of `{name, type, nullable?, default?, index?}`
- `relations[]`: list with `type`, `model`, `fk`, `onDelete`
- `indexes[]`: additional composite or full‑text indexes
- `policies?`: whether to generate policy stubs

Acceptance
- `php artisan migrate:fresh --seed` succeeds.
- Factories generate valid example data.

Prompt
```
You are the Data Modeling Agent. Create a model package for {name}.

Specs:
- Fields: {fields}
- Relations: {relations}
- Indexes: {indexes}
- Policies: {policies}

Tasks:
- Create migration, Eloquent model, factory, and a seeder stub.
- Ensure timestamps and soft deletes if needed.
- Add indexes and foreign keys with proper `onDelete` behavior.

Definition of Done:
- Fresh migrate + seed passes.
```

---

## TEMPLATE-BE-001 — CRUD controller + Form Requests

Inputs
- `model`: target Eloquent model
- `routes`: RESTful routes desired
- `validation_rules`: create/update rules

Acceptance
- Routes registered; requests validated; policies enforced if applicable.
- Feature tests for key flows pass.

Prompt
```
You are the Backend Agent. Implement CRUD for {model}.

Tasks:
- Create a controller with actions for {routes}.
- Add FormRequest classes with {validation_rules}.
- Register routes and apply policies or gates where required.
- Return JSON for API endpoints or Blade responses for web as appropriate.

Definition of Done:
- Feature tests green; validation failures handled cleanly.
```

---

## TEMPLATE-BE-002 — Queue job (image/video)

Inputs
- `source_path`: uploaded file path
- `operations[]`: e.g., resize, optimize, transcode
- `outputs[]`: desired derivatives and storage paths

Acceptance
- Jobs run async, idempotent, and error‑handled.
- Derivatives saved in expected locations.

Prompt
```
You are the Backend Agent. Create an async job pipeline for media processing.

Inputs:
- Source: {source_path}
- Operations: {operations}
- Outputs: {outputs}

Tasks:
- Implement a Job (or Jobs) that process images/videos with retries.
- Use intervention/image for images; integrate spatie/image-optimizer.
- Emit events/logs; handle failures without blocking the request thread.

Definition of Done:
- Derivatives exist and are linked to the original record.
```

---

## TEMPLATE-FE-001 — Blade view (Tailwind/Preline)

Inputs
- `layout_slot`: where content mounts in the base layout
- `components`: sections/partials to build
- `htmx_interactions`: dynamic behaviors

Acceptance
- Lighthouse ≥ 90; CSS ≤ 20 KB.
- Works on mobile and desktop.

Prompt
```
You are the Frontend Agent. Build Blade views.

Specs:
- Layout slot: {layout_slot}
- Components: {components}
- HTMX interactions: {htmx_interactions}

Tasks:
- Create Blade templates and partials using Tailwind and Preline classes.
- Keep CSS budget under 20 KB and avoid heavy JS.
- Add GLightbox for gallery where needed.

Definition of Done:
- Pages render cleanly; Lighthouse scores ≥ 90.
```

---

## TEMPLATE-TEST-001 — Pest feature test

Inputs
- `route`: target route
- `scenario`: brief description
- `assertions`: expected outcomes

Acceptance
- Test passes locally and in CI.

Prompt
```
You are the Testing & CI Agent. Write a Pest feature test.

Scenario: {scenario}
Route: {route}
Assertions: {assertions}

Tasks:
- Arrange with factories; Act via HTTP; Assert responses and DB state.
- Mock captcha or external services where necessary.

Definition of Done:
- Test runs green locally and in CI pipeline.
```

---

## TEMPLATE-MCP-001 — Ad‑hoc Screenshot via MCP

Inputs
- `route`: e.g., `/gallery`
- `device`: `desktop` or `mobile` (default `desktop`)
- `name?`: output filename (default derived from route and device)

Acceptance
- Screenshot saved under `mcp-artifacts/` and reflects current UI.

Prompt
```
You are the Testing & CI Agent. Use the MCP Playwright server to capture a screenshot for quick UI review.

Task:
- Navigate to {route} and wait for network idle.
- Take a full‑page screenshot on {device}.
- Save it as {name} (if provided) or use a route‑based default.

Report back:
- Path to the saved file and any notable rendering issues.
```
