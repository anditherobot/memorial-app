import fs from 'node:fs';
import { execSync } from 'node:child_process';

export default async function globalSetup() {
  const dbPath = 'database/testing.sqlite';
  fs.mkdirSync('database', { recursive: true });
  if (!fs.existsSync(dbPath)) {
    fs.writeFileSync(dbPath, '');
  }
  // Fresh migrate and seed for deterministic visuals
  execSync('php artisan migrate:fresh --seed --env=testing', {
    stdio: 'inherit',
  });
}

