import { test, expect } from '@playwright/test';
import * as path from 'path';

const TEST_IMAGE_PATH = path.resolve('./assets/flyer-english.png');

test.describe('Bulk Image Optimization', () => {

  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await expect(page).toHaveURL('/admin');
  });

  test('should allow bulk optimization of images', async ({ page }) => {
    await page.goto('/admin/gallery');

    // 1. Upload a new image to ensure we have a known un-optimized image
    const fileChooserPromise = page.waitForEvent('filechooser');
    await page.locator('input[type="file"]').click();
    const fileChooser = await fileChooserPromise;
    await fileChooser.setFiles(TEST_IMAGE_PATH);
    await page.click('button[type="submit"]');

    // Wait for the upload and processing to complete, then reload to see the new image
    // A simple wait should be sufficient for local dev, but a better wait would be for the image to appear.
    await page.waitForTimeout(2000);
    await page.reload();
    
    // Find the newly uploaded image. Let's assume it's the first one.
    const firstImage = page.locator('#mediaGrid > div').first();
    
    // 2. Verify it does NOT have an optimized badge initially
    await expect(firstImage.locator('.badge-optimized')).not.toBeVisible();

    // 3. Select the image
    const checkbox = firstImage.locator('.media-select-checkbox');
    await checkbox.check();
    await expect(checkbox).toBeChecked();

    // 4. Verify the optimize button is enabled and has the correct text
    const optimizeBtn = page.locator('#optimizeBtn');
    await expect(optimizeBtn).toBeEnabled();
    await expect(optimizeBtn).toContainText('Optimize 1 Selected');

    // 5. Click the optimize button
    await optimizeBtn.click();

    // 6. Wait for optimization and page reload
    await expect(optimizeBtn).toBeDisabled();
    await expect(optimizeBtn).toContainText('Optimizing...');
    await page.waitForURL('/admin/gallery'); // Wait for reload

    // 7. Verify the success toast is shown
    const successToast = page.locator('#successToast');
    await expect(successToast).toBeVisible();
    await expect(successToast).toContainText('Optimization process started');

    // 8. Find the image again and verify it now has the optimized badge
    const optimizedImage = page.locator('#mediaGrid > div').first();
    await expect(optimizedImage.locator('.badge-optimized')).toBeVisible();

    // 9. Verify the checkbox is no longer checked
    const newCheckbox = optimizedImage.locator('.media-select-checkbox');
    await expect(newCheckbox).not.toBeChecked();
  });

});
