# Memorial Website

This project is a memorial website designed to celebrate the life of a loved one. It provides a space for friends and family to share memories, wishes, and photos. The site is built with Laravel 11, SQLite, and TailwindCSS, and includes a simple admin panel for content moderation.

## Features

*   **Gallery:** A public gallery of photos and videos.
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
    *   In one terminal, run:
        ```bash
        php artisan serve
        ```
    *   In another terminal, run:
        ```bash
        npm run dev
        ```

The application will be available at `http://127.0.0.1:8000`. The admin panel is at `/admin` with default credentials `admin@example.com` / `secret`.

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