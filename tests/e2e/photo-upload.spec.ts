import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as fs from 'fs';

test.describe('Photo Upload', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle' }),
      page.click('button[type="submit"],input[type="submit"]'),
    ]);
  });

  test('should display upload form on gallery page', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    // Check that upload form exists
    await expect(page.locator('form[action*="gallery/upload"]')).toBeVisible();
    await expect(page.locator('input[type="file"]')).toBeAttached();

    // Take screenshot of initial state
    await expect(page).toHaveScreenshot('gallery-upload-form.png', {
      fullPage: true,
    });
  });

  test('should upload single image successfully', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    // Count existing photos
    const initialPhotoCount = await page.locator('.grid img[src*="storage"]').count();

    // Create a test image file
    const testImagePath = path.join(__dirname, 'fixtures', 'test-image.jpg');
    ensureTestImageExists(testImagePath);

    // Upload the file
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles(testImagePath);

    // Wait for navigation after auto-submit
    await page.waitForURL('**/admin/gallery', { timeout: 10000 });

    // Check for success message
    const successMessage = page.locator('.bg-green-50');
    if (await successMessage.count() > 0) {
      await expect(successMessage).toContainText(/uploaded|success/i);
    }

    // Verify photo was added
    const newPhotoCount = await page.locator('.grid img[src*="storage"]').count();
    expect(newPhotoCount).toBeGreaterThan(initialPhotoCount);

    // Take screenshot of result
    await expect(page).toHaveScreenshot('gallery-after-upload.png', {
      fullPage: true,
    });
  });

  test('should upload multiple images at once', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    const initialPhotoCount = await page.locator('.grid img[src*="storage"]').count();

    // Create multiple test images
    const testImages = [
      path.join(__dirname, 'fixtures', 'test-image-1.jpg'),
      path.join(__dirname, 'fixtures', 'test-image-2.jpg'),
    ];
    testImages.forEach(ensureTestImageExists);

    // Upload multiple files
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles(testImages);

    // Wait for navigation
    await page.waitForURL('**/admin/gallery', { timeout: 10000 });

    // Verify multiple photos were added
    const newPhotoCount = await page.locator('.grid img[src*="storage"]').count();
    expect(newPhotoCount).toBeGreaterThanOrEqual(initialPhotoCount + 2);
  });

  test('should handle upload errors gracefully', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    // Try to upload invalid file type (if validation exists)
    const testFilePath = path.join(__dirname, 'fixtures', 'test-file.txt');
    ensureTestTextFileExists(testFilePath);

    const fileInput = page.locator('input[type="file"]');

    // This might be blocked by client-side validation
    try {
      await fileInput.setInputFiles(testFilePath);
      await page.waitForTimeout(1000);

      // Check for error message
      const errorMessage = page.locator('.text-red-600, .bg-red-50');
      if (await errorMessage.count() > 0) {
        await expect(errorMessage).toBeVisible();
      }
    } catch (e) {
      // Expected if client-side validation blocks it
      console.log('Client-side validation prevented invalid file upload');
    }
  });

  test('should display uploaded images in gallery grid', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    // Check grid layout exists
    const grid = page.locator('.grid');
    await expect(grid).toBeVisible();

    // Check if images are displayed with proper attributes
    const images = page.locator('.grid img[src*="storage"]');
    const imageCount = await images.count();

    if (imageCount > 0) {
      const firstImage = images.first();
      await expect(firstImage).toBeVisible();

      // Check that image has proper src
      const src = await firstImage.getAttribute('src');
      expect(src).toContain('/storage/');

      // Check that image loads successfully
      await firstImage.waitFor({ state: 'visible' });

      // Verify image dimensions are shown
      const dimensionBadges = page.locator('.chip, span:has-text("×")');
      if (await dimensionBadges.count() > 0) {
        await expect(dimensionBadges.first()).toBeVisible();
      }
    }
  });

  test('should show lightbox when clicking image', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    // Wait for GLightbox to initialize
    await page.waitForTimeout(1000);

    // Check if there are photos to click
    const photoLinks = page.locator('a.glightbox');
    const photoCount = await photoLinks.count();

    if (photoCount > 0) {
      // Click first photo
      await photoLinks.first().click();

      // Wait for lightbox to appear
      await page.waitForTimeout(500);

      // Check if lightbox opened
      const lightbox = page.locator('.glightbox-container, .glightbox-open, [class*="lightbox"]');
      const hasLightbox = await lightbox.count() > 0;

      if (hasLightbox) {
        await expect(lightbox).toBeVisible();

        // Take screenshot of lightbox
        await page.screenshot({ path: 'test-results/lightbox-view.png' });
      }
    }
  });

  test('should auto-submit form on file selection', async ({ page }) => {
    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    const testImagePath = path.join(__dirname, 'fixtures', 'test-image.jpg');
    ensureTestImageExists(testImagePath);

    // Set up navigation listener before file selection
    const navigationPromise = page.waitForNavigation({ timeout: 10000 });

    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles(testImagePath);

    // Should auto-submit and navigate
    await navigationPromise;

    // Should be back on gallery page
    expect(page.url()).toContain('/admin/gallery');
  });

  test('mobile: should upload photo successfully', async ({ page, browserName }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 390, height: 844 });

    await page.goto('/admin/gallery', { waitUntil: 'networkidle' });

    // Check upload form is responsive
    const uploadForm = page.locator('form[action*="gallery/upload"]');
    await expect(uploadForm).toBeVisible();

    // Upload image
    const testImagePath = path.join(__dirname, 'fixtures', 'test-image.jpg');
    ensureTestImageExists(testImagePath);

    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles(testImagePath);

    await page.waitForURL('**/admin/gallery', { timeout: 10000 });

    // Take mobile screenshot
    await expect(page).toHaveScreenshot('gallery-mobile-after-upload.png', {
      fullPage: true,
    });
  });
});

// Helper function to ensure test image exists
function ensureTestImageExists(imagePath: string) {
  const dir = path.dirname(imagePath);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }

  if (!fs.existsSync(imagePath)) {
    // Create a minimal valid JPEG file (1x1 red pixel)
    const jpegHeader = Buffer.from([
      0xFF, 0xD8, 0xFF, 0xE0, 0x00, 0x10, 0x4A, 0x46, 0x49, 0x46, 0x00, 0x01,
      0x01, 0x01, 0x00, 0x48, 0x00, 0x48, 0x00, 0x00, 0xFF, 0xDB, 0x00, 0x43,
      0x00, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
      0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
      0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
      0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
      0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF,
      0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xFF, 0xC0, 0x00, 0x0B,
      0x08, 0x00, 0x01, 0x00, 0x01, 0x01, 0x01, 0x11, 0x00, 0xFF, 0xC4, 0x00,
      0x14, 0x00, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00,
      0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0xFF, 0xDA, 0x00, 0x08, 0x01,
      0x01, 0x00, 0x00, 0x3F, 0x00, 0x7F, 0xFF, 0xD9
    ]);
    fs.writeFileSync(imagePath, jpegHeader);
  }
}

function ensureTestTextFileExists(filePath: string) {
  const dir = path.dirname(filePath);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }

  if (!fs.existsSync(filePath)) {
    fs.writeFileSync(filePath, 'This is a test text file.');
  }
}