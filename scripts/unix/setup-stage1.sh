#!/usr/bin/env bash
set -euo pipefail

APP_DIR=${1:-app}

echo "=== Stage 1: Setup Agent (Unix) ==="

need() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "Missing required command: $1" >&2
    exit 1
  fi
}

need git
need php
need composer
need node
need npm

if [ ! -d "$APP_DIR" ]; then
  echo "Creating Laravel 11 app in '$APP_DIR'..."
  composer create-project laravel/laravel:^11 "$APP_DIR"
else
  echo "Laravel app directory '$APP_DIR' already exists. Skipping create-project."
fi

pushd "$APP_DIR" >/dev/null

# SQLite env
if [ ! -f .env ]; then cp .env.example .env; fi
sed -i.bak \
  -e 's/^APP_NAME=.*/APP_NAME="Memorial"/' \
  -e 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' \
  -e 's/^DB_HOST=.*/# DB_HOST=127.0.0.1/' \
  -e 's/^DB_PORT=.*/# DB_PORT=3306/' \
  -e 's#^DB_DATABASE=.*#DB_DATABASE=database/database.sqlite#' \
  -e 's/^DB_USERNAME=.*/# DB_USERNAME=/' \
  -e 's/^DB_PASSWORD=.*/# DB_PASSWORD=/' .env
rm -f .env.bak || true

mkdir -p database
touch database/database.sqlite

php artisan key:generate

echo "Installing Tailwind + Preline..."
npm pkg set type="module" >/dev/null 2>&1 || true
npm install -D tailwindcss postcss autoprefixer
npm install preline
if [ ! -f tailwind.config.js ]; then npx tailwindcss init -p; fi

# Patch tailwind.config.js for content + plugin
node <<'NODE'
const fs = require('fs');
const p = 'tailwind.config.js';
let s = fs.readFileSync(p, 'utf8');
s = s.replace(/content:\s*\[[\s\S]*?\],/, `content: [
  './resources/**/*.blade.php',
  './resources/**/*.js',
  './resources/**/*.vue',
  './node_modules/preline/dist/*.js',
],`);
if (!/plugins:\s*\[.*preline/.test(s)) {
  s = s.replace(/plugins:\s*\[\]/, "plugins: [require('preline/plugin')]");
}
fs.writeFileSync(p, s);
NODE

# Ensure Tailwind directives
mkdir -p resources/css
cat > resources/css/app.css <<'CSS'
@tailwind base;
@tailwind components;
@tailwind utilities;
CSS

# Base layout
mkdir -p resources/views/layouts
cat > resources/views/layouts/app.blade.php <<'BLADE'
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Memorial') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body class="min-h-screen bg-gray-50 text-gray-900">
    <header class="px-4 py-3 border-b bg-white">
      <div class="max-w-5xl mx-auto flex items-center justify-between">
        <a href="/" class="font-semibold">{{ config('app.name','Memorial') }}</a>
      </div>
    </header>
    <main class="max-w-5xl mx-auto p-4">
      {{ $slot ?? '' }}
      @yield('content')
    </main>
    <footer class="max-w-5xl mx-auto p-4 text-sm text-gray-500">&copy; {{ date('Y') }}</footer>

    <!-- HTMX, Alpine, GLightbox (CDN) -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <!-- Preline (uses Tailwind) -->
    <script type="module">import "preline";</script>
  </body>
</html>
BLADE

echo "Installing image packages..."
composer require intervention/image:^3 spatie/image-optimizer:^1 --no-interaction

echo "Building frontend assets..."
npm run build

popd >/dev/null

echo "=== Setup complete ==="
echo "Laravel app in '$APP_DIR'. Start dev with:"
echo "  cd $APP_DIR; php artisan serve"
echo "  cd $APP_DIR; npm run dev"
