# Memorial Website

This project is a memorial website designed to celebrate the life of a loved one. It provides a space for friends and family to share memories, wishes, and photos. The site is built with Laravel 11, SQLite, and TailwindCSS, and includes a simple admin panel for content moderation.

## Features

*   **Gallery:** A public gallery of photos and videos.
*   **Image Optimization:** Automatic image optimization on upload
    - Thumbnails: <200KB for fast grid loading
    - Web-optimized: <2MB for lightbox viewing
    - Originals preserved for downloads
    - Shows file size savings in UI
*   **Wishwall:** A place for guests to leave messages and wishes.
*   **Updates:** A section for posting announcements and updates.
*   **Admin Panel:** A simple admin panel for moderating content, managing uploads, and posting updates.

## Tech Stack

*   **Backend:** Laravel 11
*   **Database:** SQLite
*   **Frontend:** TailwindCSS, Vite, Preline, HTMX/Alpine (via CDN)
*   **Testing:** Pest (unit/feature), Playwright (visual)

## Getting Started

### Prerequisites

*   PHP
*   Composer
*   Node.js 18+

### Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/your-username/memorial-website.git
    cd memorial-website
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm ci
    ```

3.  **Set up the database:**
    ```bash
    touch database/dev.sqlite
    ```

4.  **Run migrations and seed the database:**
    ```bash
    php artisan migrate:fresh --seed
    ```

5.  **Start the development servers:**
    *   **Terminal 1** - Laravel server:
        ```bash
        php artisan serve
        ```
    *   **Terminal 2** - Vite dev server:
        ```bash
        npm run dev
        ```
    *   **Terminal 3** - Queue worker (REQUIRED for image optimization):
        ```bash
        php artisan queue:work
        ```

The application will be available at `http://127.0.0.1:8000`. The admin panel is at `/admin` with default credentials `admin@example.com` / `secret`.

> **⚠️ IMPORTANT:** The queue worker (Terminal 3) **must be running** for image optimization to work. Without it, uploaded images will not be optimized and will stay as "Not Optimized". See [`docs/QUEUE_WORKER_SETUP.md`](docs/QUEUE_WORKER_SETUP.md) for details.

## Visual UI Checks (Playwright + MCP)

This project uses Playwright for visual regression testing. The following commands are available:

*   **Run all visual checks:**
    ```bash
    npm run ui:check
    ```
*   **Run desktop checks only:**
    ```bash
    npm run ui:desk
    ```
*   **Run mobile checks only:**
    ```bash
    npm run ui:mobile
    ```
*   **Update baseline screenshots:**
    ```bash
    npm run ui:update
    ```

For more information on the development process and agentic workflow, please see `AGENTS.md`.

## Documentation

### Image Optimization
- **[Queue Worker Setup](docs/QUEUE_WORKER_SETUP.md)** - Critical setup guide for queue worker
- **[Optimization Flow](docs/OPTIMIZATION_FLOW.md)** - How image optimization works
- **[Testing Guide](TESTING_GUIDE.md)** - Complete testing scenarios
- **[Implementation Status](docs/IMPLEMENTATION_STATUS.md)** - Current feature status

### Development
- **[DEV_LOG.md](DEV_LOG.md)** - Development history and changes
- **[AGENTS.md](AGENTS.md)** - Agentic workflow documentation

### Key Points

**Image Optimization:**
- Automatic on upload (queue-based)
- Creates 2 derivatives: thumbnail (<200KB) + web-optimized (<2MB)
- Originals preserved for downloads
- UI shows before/after file sizes
- Requires queue worker: `php artisan queue:work`

**Testing:**
- PHPUnit: `php artisan test` (9 optimization tests)
- Playwright: `npx playwright test` (E2E tests)
- Visual regression: `npm run ui:check`