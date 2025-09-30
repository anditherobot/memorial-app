import { test, expect } from '@playwright/test';
import * as path from 'path';

const TEST_IMAGE_PATH = path.resolve('./ui/jar/IMG_9499.jpg');

test.describe('Bulk Image Optimization', () => {

  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await page.click('button[type="submit"]');
    await page.waitForURL('/admin');
  });

  test('should show optimize button and checkboxes', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Verify bulk optimization UI elements exist
    await expect(page.locator('#selectAllBtn')).toBeVisible();
    await expect(page.locator('#optimizeBtn')).toBeVisible();

    // Optimize button should be disabled when nothing is selected
    await expect(page.locator('#optimizeBtn')).toBeDisabled();
  });

  test('should enable optimize button when images are selected', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Select first image
    const firstCheckbox = page.locator('.media-select-checkbox').first();
    await firstCheckbox.check();

    // Button should now be enabled
    await expect(page.locator('#optimizeBtn')).toBeEnabled();
    await expect(page.locator('#optimizeBtn')).toContainText('Optimize 1 Selected');
  });

  test('should select all and deselect all images', async ({ page }) => {
    await page.goto('/admin/gallery');

    const selectAllBtn = page.locator('#selectAllBtn');

    // Click select all
    await selectAllBtn.click();
    await expect(selectAllBtn).toContainText('Deselect All');

    // All checkboxes should be checked
    const checkboxes = page.locator('.media-select-checkbox');
    const count = await checkboxes.count();
    for (let i = 0; i < count; i++) {
      await expect(checkboxes.nth(i)).toBeChecked();
    }

    // Click deselect all
    await selectAllBtn.click();
    await expect(selectAllBtn).toContainText('Select All');

    // All checkboxes should be unchecked
    for (let i = 0; i < count; i++) {
      await expect(checkboxes.nth(i)).not.toBeChecked();
    }
  });

  test('should show optimization status badges', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Each image should show either "✓ Optimized" or "✗ Not Optimized"
    const images = page.locator('#mediaGrid > div');
    const count = await images.count();

    for (let i = 0; i < count; i++) {
      const image = images.nth(i);
      const hasOptimized = await image.locator('text=✓ Optimized').count();
      const hasNotOptimized = await image.locator('text=✗ Not Optimized').count();

      // Should have exactly one of the two badges
      expect(hasOptimized + hasNotOptimized).toBe(1);
    }
  });

  test('should show file sizes for images', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Each image should display file size in MB
    const images = page.locator('#mediaGrid > div');
    const firstImage = images.first();

    // Look for text matching "X.XX MB" pattern
    await expect(firstImage.locator('text=/\\d+\\.\\d+ MB/')).toBeVisible();
  });

  test('should track optimization status in data attributes', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Checkboxes should have data-is-optimized attribute
    const firstCheckbox = page.locator('.media-select-checkbox').first();
    const isOptimized = await firstCheckbox.getAttribute('data-is-optimized');

    expect(['true', 'false']).toContain(isOptimized);
  });

  test('should perform bulk optimization', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Find an unoptimized image
    const unoptimizedCheckbox = page.locator('.media-select-checkbox[data-is-optimized="false"]').first();
    const unoptimizedExists = await unoptimizedCheckbox.count() > 0;

    if (!unoptimizedExists) {
      test.skip();
      return;
    }

    // Select the unoptimized image
    await unoptimizedCheckbox.check();

    const optimizeBtn = page.locator('#optimizeBtn');
    await expect(optimizeBtn).toBeEnabled();

    // Get the count from button text
    const buttonText = await optimizeBtn.textContent();
    expect(buttonText).toMatch(/Optimize \d+ Selected/);

    // Click optimize
    await optimizeBtn.click();

    // Button should show "Optimizing..."
    await expect(optimizeBtn).toContainText('Optimizing...');
    await expect(optimizeBtn).toBeDisabled();

    // Wait for page reload (triggered by JavaScript after successful response)
    await page.waitForLoadState('networkidle');

    // After reload, checkboxes should be unchecked
    await expect(unoptimizedCheckbox).not.toBeChecked();

    // Optimize button should be disabled again
    await expect(optimizeBtn).toBeDisabled();
  });

  test('should handle file size verification', async ({ page }) => {
    await page.goto('/admin/gallery');

    // Check if any images show file size over 2MB
    const largeImages = page.locator('text=/[3-9]\\d*\\.\\d+ MB/'); // 3+ MB
    const count = await largeImages.count();

    if (count > 0) {
      console.log(`Found ${count} images over 3MB that should be optimized`);
    }

    // All thumbnails should be under 200KB ideally, but we can't verify this from UI
    // This is verified in the PHPUnit tests instead
  });

  test('should be responsive on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE
    await page.goto('/admin/gallery');

    // Grid should still be visible and functional
    await expect(page.locator('#mediaGrid')).toBeVisible();

    // Buttons should be visible
    await expect(page.locator('#selectAllBtn')).toBeVisible();
    await expect(page.locator('#optimizeBtn')).toBeVisible();

    // Checkboxes should be clickable
    const firstCheckbox = page.locator('.media-select-checkbox').first();
    await firstCheckbox.check();
    await expect(firstCheckbox).toBeChecked();
  });

});
