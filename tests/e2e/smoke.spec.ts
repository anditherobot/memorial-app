import { test, expect } from '@playwright/test';

// Helper: friendly name for current device from project name
function deviceFromProject(projectName: string) {
  return projectName.includes('mobile') ? 'mobile' : 'desktop';
}

const routes = [
  { path: '/', name: 'home' },
  { path: '/gallery', name: 'gallery' },
  { path: '/wishes', name: 'wishes' },
  { path: '/updates', name: 'updates' },
];

test.describe('Public pages', () => {
  for (const r of routes) {
    test(`${r.name} renders`, async ({ page }, testInfo) => {
      const device = deviceFromProject(testInfo.project.name);
      await page.goto(r.path, { waitUntil: 'networkidle' });
      await expect(page).toHaveScreenshot(`${r.name}.${device}.png`, {
        fullPage: true,
      });
    });
  }
});

test.describe('Updates show (first)', () => {
  test('renders first update', async ({ page }, testInfo) => {
    const device = deviceFromProject(testInfo.project.name);
    await page.goto('/updates', { waitUntil: 'networkidle' });
    const firstLink = page.locator('a[href^="/updates/"]').first();
    if (await firstLink.count()) {
      await firstLink.click();
      await page.waitForLoadState('networkidle');
      await expect(page).toHaveScreenshot(`updates-show.${device}.png`, {
        fullPage: true,
      });
    } else {
      test.skip(true, 'No updates seeded');
    }
  });
});

test.describe('Admin pages', () => {
  test.beforeEach(async ({ page }) => {
    // Programmatic login using seeded admin credentials
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button[type="submit"],input[type="submit"]'),
    ]);
  });

  const adminRoutes = [
    { path: '/admin', name: 'admin-dashboard' },
    { path: '/admin/wishes', name: 'admin-wishes' },
    { path: '/admin/updates', name: 'admin-updates' },
  ];

  for (const r of adminRoutes) {
    test(`${r.name} renders`, async ({ page }, testInfo) => {
      const device = deviceFromProject(testInfo.project.name);
      await page.goto(r.path, { waitUntil: 'networkidle' });
      await expect(page).toHaveScreenshot(`${r.name}.${device}.png`, {
        fullPage: true,
      });
    });
  }
});

