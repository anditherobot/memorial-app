import { defineConfig } from '@playwright/test';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const APP_URL = process.env.APP_URL || 'http://127.0.0.1:8000';

export default defineConfig({
  testDir: 'tests/e2e',
  globalSetup: resolve(__dirname, './tests/e2e/global-setup.ts'),
  fullyParallel: false,
  timeout: 60_000,
  expect: { timeout: 10_000 },
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: APP_URL,
    screenshot: 'only-on-failure',
    video: 'off',
    trace: 'retain-on-failure',
    colorScheme: 'light',
    reducedMotion: 'reduce',
    viewport: { width: 1280, height: 800 },
  },
  snapshotPathTemplate: '{testDir}/__screenshots__/{projectName}/{arg}{ext}',
  projects: [
    {
      name: 'chromium-desktop',
      use: { browserName: 'chromium', viewport: { width: 1280, height: 800 } },
    },
    {
      name: 'chromium-mobile',
      use: {
        browserName: 'chromium',
        viewport: { width: 390, height: 844 },
        userAgent:
          'Mozilla/5.0 (Linux; Android 12; Pixel 5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36',
      },
    },
  ],
});

