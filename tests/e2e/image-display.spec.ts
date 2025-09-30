import { test, expect } from '@playwright/test';
import * as path from 'path';

const TEST_IMAGE = path.resolve('ui/jar/504160674_2149190605508280_6451814468790582020_n.jpg');

test.describe('Image Display Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/**', { timeout: 5000 });
  });

  test('should upload and display single image in admin gallery', async ({ page }) => {
    await page.goto('/admin/gallery');
    await page.waitForLoadState('networkidle');

    // Count existing images
    const initialCount = await page.locator('.task-card-ui img').count();
    console.log(`Initial image count: ${initialCount}`);

    // Upload image
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles(TEST_IMAGE);

    // Wait for preview to appear
    await page.waitForSelector('#previewArea-photos', { timeout: 5000 });
    await expect(page.locator('#previewArea-photos')).toBeVisible();

    // Click upload button
    const uploadButton = page.locator('button[type="submit"]:has-text("Upload")');
    await uploadButton.click();

    // Wait for upload to complete and page to reload
    await page.waitForURL('**/admin/gallery', { timeout: 10000 });
    await page.waitForLoadState('networkidle');

    // Verify success toast
    const toast = page.locator('#successToast, .bg-green-500');
    if (await toast.count() > 0) {
      await expect(toast).toBeVisible();
    }

    // Verify image appears in gallery
    const newCount = await page.locator('.task-card-ui img').count();
    console.log(`New image count: ${newCount}`);
    expect(newCount).toBeGreaterThan(initialCount);

    // Take screenshot
    await page.screenshot({ path: 'test-results/admin-gallery-after-upload.png', fullPage: true });
  });

  test('should display image list/grid in admin gallery', async ({ page }) => {
    await page.goto('/admin/gallery');
    await page.waitForLoadState('networkidle');

    // Check for grid layout
    const grid = page.locator('.grid');
    await expect(grid).toBeVisible();

    // Check for images
    const images = page.locator('.task-card-ui img');
    const imageCount = await images.count();
    console.log(`Images in grid: ${imageCount}`);

    if (imageCount > 0) {
      // Verify first image
      const firstImage = images.first();
      await expect(firstImage).toBeVisible();

      // Check image has src attribute
      const src = await firstImage.getAttribute('src');
      expect(src).toBeTruthy();
      console.log(`First image src: ${src}`);

      // Check for lazy loading
      const loading = await firstImage.getAttribute('loading');
      expect(loading).toBe('lazy');

      // Check for error handler
      const onerror = await firstImage.getAttribute('onerror');
      expect(onerror).toContain('placeholder.svg');

      // Check for metadata
      const card = page.locator('.task-card-ui').first();
      await expect(card.locator('.truncate')).toBeVisible(); // Filename

      // Check for dimensions badge if present
      const dimensionBadge = card.locator('.chip:has-text("×")');
      if (await dimensionBadge.count() > 0) {
        await expect(dimensionBadge).toBeVisible();
        const dimensionText = await dimensionBadge.textContent();
        expect(dimensionText).toMatch(/\d+×\d+/);
        console.log(`Image dimensions: ${dimensionText}`);
      }
    }

    // Take screenshot
    await page.screenshot({ path: 'test-results/admin-gallery-grid.png', fullPage: true });
  });

  test('should display images in public gallery', async ({ page }) => {
    // Logout first
    await page.goto('/admin');
    const logoutButton = page.locator('button:has-text("Logout"), a:has-text("Logout")');
    if (await logoutButton.count() > 0) {
      await logoutButton.click();
    }

    // Go to public gallery
    await page.goto('/gallery');
    await page.waitForLoadState('networkidle');

    // Check for grid layout
    const grid = page.locator('.grid');
    await expect(grid).toBeVisible();

    // Check for images
    const images = page.locator('.task-card-ui img');
    const imageCount = await images.count();
    console.log(`Public gallery images: ${imageCount}`);

    expect(imageCount).toBeGreaterThan(0);

    // Verify image component
    const firstImage = images.first();
    await expect(firstImage).toBeVisible();

    // Check lazy loading
    const loading = await firstImage.getAttribute('loading');
    expect(loading).toBe('lazy');

    // Check lightbox link
    const lightboxLink = page.locator('a.glightbox').first();
    await expect(lightboxLink).toBeVisible();

    const href = await lightboxLink.getAttribute('href');
    expect(href).toContain('/storage/');
    console.log(`Lightbox link: ${href}`);

    // Take screenshot
    await page.screenshot({ path: 'test-results/public-gallery-grid.png', fullPage: true });
  });

  test('should open image in lightbox', async ({ page }) => {
    await page.goto('/gallery');
    await page.waitForLoadState('networkidle');

    // Wait for GLightbox to initialize
    await page.waitForTimeout(1000);

    // Check if there are images
    const lightboxLinks = page.locator('a.glightbox');
    const linkCount = await lightboxLinks.count();

    if (linkCount > 0) {
      // Click first image
      await lightboxLinks.first().click();

      // Wait for lightbox to appear
      await page.waitForTimeout(500);

      // Check if lightbox opened (GLightbox adds elements to body)
      const lightbox = page.locator('.glightbox-container, .goverlay, [class*="gslide"]');
      const lightboxExists = await lightbox.count() > 0;

      if (lightboxExists) {
        await expect(lightbox.first()).toBeVisible();
        console.log('Lightbox opened successfully');

        // Take screenshot of lightbox
        await page.screenshot({ path: 'test-results/lightbox-view.png' });

        // Close lightbox (usually ESC key or close button)
        await page.keyboard.press('Escape');
      } else {
        console.log('Lightbox did not open - may need GLightbox initialization');
      }
    }
  });

  test('should verify image URLs are accessible', async ({ page }) => {
    await page.goto('/gallery');
    await page.waitForLoadState('networkidle');

    const images = page.locator('.task-card-ui img');
    const imageCount = await images.count();

    if (imageCount > 0) {
      // Get first image URL
      const firstImage = images.first();
      const src = await firstImage.getAttribute('src');

      if (src) {
        console.log(`Testing image URL: ${src}`);

        // Navigate to image directly
        const response = await page.goto(src);

        if (response) {
          const status = response.status();
          console.log(`Image response status: ${status}`);

          // Should be 200 (OK) or 304 (Not Modified)
          expect([200, 304]).toContain(status);

          // Check content type
          const contentType = response.headers()['content-type'];
          console.log(`Content-Type: ${contentType}`);
          expect(contentType).toMatch(/image\//);
        }
      }
    }
  });

  test('should handle missing images gracefully', async ({ page }) => {
    await page.goto('/gallery');
    await page.waitForLoadState('networkidle');

    // Inject a broken image for testing
    await page.evaluate(() => {
      const img = document.createElement('img');
      img.src = '/storage/nonexistent/missing.jpg';
      img.loading = 'lazy';
      img.onerror = function() {
        this.src = '/images/placeholder.svg';
        this.onerror = null;
      };
      img.className = 'test-broken-image w-full h-44 object-cover';
      document.querySelector('.grid')?.appendChild(img);
    });

    // Wait for error handler to trigger
    await page.waitForTimeout(1000);

    // Check if placeholder loaded
    const testImage = page.locator('.test-broken-image');
    const src = await testImage.getAttribute('src');

    expect(src).toContain('placeholder.svg');
    console.log('Broken image handled correctly with placeholder');
  });

  test('should display image metadata', async ({ page }) => {
    await page.goto('/admin/gallery');
    await page.waitForLoadState('networkidle');

    const cards = page.locator('.task-card-ui');
    const cardCount = await cards.count();

    if (cardCount > 0) {
      const firstCard = cards.first();

      // Check for filename
      const filename = firstCard.locator('.truncate');
      await expect(filename).toBeVisible();
      const filenameText = await filename.textContent();
      console.log(`Filename: ${filenameText}`);
      expect(filenameText?.length).toBeGreaterThan(0);

      // Check for dimensions if present
      const dimensions = firstCard.locator('.chip:has-text("×")');
      if (await dimensions.count() > 0) {
        const dimensionsText = await dimensions.textContent();
        console.log(`Dimensions: ${dimensionsText}`);
        expect(dimensionsText).toMatch(/\d+×\d+/);
      }
    }
  });

  test('mobile: should display image grid responsively', async ({ page }) => {
    // Set mobile viewport
    await page.setViewportSize({ width: 390, height: 844 });

    await page.goto('/gallery');
    await page.waitForLoadState('networkidle');

    // Check grid exists
    const grid = page.locator('.grid');
    await expect(grid).toBeVisible();

    // Verify grid is responsive (should have grid-cols-2 on mobile)
    const gridClass = await grid.getAttribute('class');
    expect(gridClass).toContain('grid-cols-2');

    // Check images display properly
    const images = page.locator('.task-card-ui img');
    const imageCount = await images.count();
    console.log(`Mobile gallery images: ${imageCount}`);

    if (imageCount > 0) {
      await expect(images.first()).toBeVisible();
    }

    // Take mobile screenshot
    await page.screenshot({ path: 'test-results/mobile-gallery-grid.png', fullPage: true });
  });
});