#Requires -Version 5.1
param(
  [string]$AppDir = "app",
  [switch]$SkipInstall
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

function Ensure-Command($name, $wingetId) {
  if (Get-Command $name -ErrorAction SilentlyContinue) { return $true }
  if ($SkipInstall) { throw "Missing command '$name'. Install it and rerun." }
  Write-Host "Installing $name via winget ($wingetId)..." -ForegroundColor Cyan
  winget install --id $wingetId -e --accept-source-agreements --accept-package-agreements | Out-Null
  if (-not (Get-Command $name -ErrorAction SilentlyContinue)) {
    throw "'$name' not found after installation. Restart your shell and retry."
  }
}

Write-Host "=== Stage 1: Setup Agent (Windows) ===" -ForegroundColor Green

# 0) Prereqs
Ensure-Command git "Git.Git"
Ensure-Command php "PHP.PHP"
Ensure-Command composer "Composer.Composer"
Ensure-Command node "OpenJS.NodeJS.LTS"
Ensure-Command npm "OpenJS.NodeJS.LTS"

# 1) Scaffold Laravel 11
if (-not (Test-Path $AppDir)) {
  Write-Host "Creating Laravel 11 app in '$AppDir'..." -ForegroundColor Cyan
  composer create-project laravel/laravel:^11 $AppDir
} else {
  Write-Host "Laravel app directory '$AppDir' already exists. Skipping create-project." -ForegroundColor Yellow
}

Push-Location $AppDir

# 2) SQLite env
if (-not (Test-Path ".env")) { Copy-Item ".env.example" ".env" }
$dbPath = Join-Path (Resolve-Path .) "database\database.sqlite"
$dbRel = 'database/database.sqlite'
(Get-Content .env) `
  -replace '^APP_NAME=.*', 'APP_NAME="Memorial"' `
  -replace '^DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' `
  -replace '^DB_HOST=.*', '# DB_HOST=127.0.0.1' `
  -replace '^DB_PORT=.*', '# DB_PORT=3306' `
  -replace '^DB_DATABASE=.*', "DB_DATABASE=$dbRel" `
  -replace '^DB_USERNAME=.*', '# DB_USERNAME=' `
  -replace '^DB_PASSWORD=.*', '# DB_PASSWORD=' `
  | Set-Content .env
New-Item -ItemType Directory -Force -Path (Split-Path $dbPath) | Out-Null
if (-not (Test-Path $dbPath)) { New-Item -ItemType File -Path $dbPath | Out-Null }

php artisan key:generate

# 3) Tailwind + Preline
Write-Host "Installing Tailwind + Preline..." -ForegroundColor Cyan
npm pkg set type="module" | Out-Null
npm install -D tailwindcss postcss autoprefixer
npm install preline
if (-not (Test-Path "tailwind.config.js")) { npx tailwindcss init -p }

# Tailwind config
$twCfg = Get-Content "tailwind.config.js" -Raw
if ($twCfg -notmatch 'preline') {
  $twCfg = $twCfg -replace 'content: \[(.|\n)*?\],', "content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './node_modules/preline/dist/*.js',
  ]," -replace 'plugins: \[\]', 'plugins: [require("preline/plugin")]'
  $twCfg | Set-Content "tailwind.config.js"
}

# Ensure Tailwind directives in app.css
$cssPath = "resources/css/app.css"
if (-not (Test-Path $cssPath)) { New-Item -ItemType File -Path $cssPath | Out-Null }
$css = Get-Content $cssPath -Raw
if ($css -notmatch '@tailwind base') {
  "@tailwind base;`n@tailwind components;`n@tailwind utilities;`n" | Set-Content $cssPath
}

# 4) Base layout with CDNs
$layoutDir = "resources/views/layouts"
New-Item -ItemType Directory -Force -Path $layoutDir | Out-Null
$layout = @'
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
'@
Set-Content "$layoutDir/app.blade.php" $layout

# 5) Composer packages for images
Write-Host "Installing image packages..." -ForegroundColor Cyan
composer require intervention/image:^3 spatie/image-optimizer:^1 --no-interaction

# 6) Build assets to verify toolchain
Write-Host "Building frontend assets..." -ForegroundColor Cyan
npm run build

Pop-Location

Write-Host "=== Setup complete ===" -ForegroundColor Green
Write-Host "Laravel app in '$AppDir'. Start dev with:" -ForegroundColor Green
Write-Host "  cd $AppDir; php artisan serve" -ForegroundColor Yellow
Write-Host "  cd $AppDir; npm run dev" -ForegroundColor Yellow

# 7) Initialize git repository (optional)
try {
  if (-not (Test-Path ".git")) {
    Write-Host "Initializing git repository..." -ForegroundColor Cyan
    git init | Out-Null
    # Set default branch to main if git supports it
    try { git symbolic-ref HEAD refs/heads/main | Out-Null } catch {}
  } else {
    Write-Host ".git already present. Skipping git init." -ForegroundColor Yellow
  }
} catch {
  Write-Host "Git not initialized (git may not be available in PATH yet)." -ForegroundColor Yellow
}
