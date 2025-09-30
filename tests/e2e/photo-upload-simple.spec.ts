import { test, expect } from '@playwright/test';
import * as path from 'path';
import * as fs from 'fs';
import { fileURLToPath } from 'url';
import { dirname } from 'path';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

test.describe('Photo Upload - Simple', () => {
  test('should access gallery upload page and analyze UI', async ({ page }) => {
    // Login as admin
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@example.com');
    await page.fill('input[name="password"]', 'secret');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin/**', { timeout: 5000 });

    // Navigate to gallery
    await page.goto('/admin/gallery');
    await page.waitForLoadState('networkidle');

    console.log('Page URL:', page.url());

    // Check upload form exists
    const uploadForm = page.locator('form[action*="gallery/upload"]');
    const formExists = await uploadForm.count() > 0;
    console.log('Upload form exists:', formExists);

    if (formExists) {
      const fileInput = page.locator('input[type="file"]');
      const inputExists = await fileInput.count() > 0;
      console.log('File input exists:', inputExists);

      if (inputExists) {
        const inputName = await fileInput.getAttribute('name');
        const acceptAttr = await fileInput.getAttribute('accept');
        const isMultiple = await fileInput.getAttribute('multiple');
        console.log('Input name:', inputName);
        console.log('Accept attribute:', acceptAttr);
        console.log('Multiple attribute:', isMultiple);
      }

      // Check for auto-submit
      const onchangeAttr = await fileInput.getAttribute('onchange');
      console.log('Onchange attribute:', onchangeAttr);
    }

    // Check current photos
    const photoGrid = page.locator('.grid');
    const gridCount = await photoGrid.count();
    console.log('Photo grids found:', gridCount);

    if (gridCount > 0) {
      const images = page.locator('.grid img[src*="storage"]');
      const imageCount = await images.count();
      console.log('Images in gallery:', imageCount);

      if (imageCount > 0) {
        const firstImg = images.first();
        const imgSrc = await firstImg.getAttribute('src');
        console.log('First image src:', imgSrc);

        // Check if image is accessible
        const response = await page.goto(imgSrc!);
        console.log('Image response status:', response?.status());
      }
    }

    // Take screenshot
    await page.screenshot({ path: 'test-results/gallery-analysis.png', fullPage: true });

    // Analysis results
    const analysis = {
      formExists,
      uploadFeatureWorking: formExists && await fileInput.count() > 0,
      autoSubmitEnabled: onchangeAttr?.includes('submit'),
      imagesDisplayed: await page.locator('.grid img[src*="storage"]').count(),
    };

    console.log('\n=== Photo Upload Analysis ===');
    console.log(JSON.stringify(analysis, null, 2));

    expect(formExists).toBeTruthy();
  });
});

// Helper to create test image
function createTestImage(imagePath: string) {
  const dir = path.dirname(imagePath);
  if (!fs.existsSync(dir)) {
    fs.mkdirSync(dir, { recursive: true });
  }

  if (!fs.existsSync(imagePath)) {
    // Minimal valid JPEG (1x1 red pixel)
    const jpegData = Buffer.from([
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
    fs.writeFileSync(imagePath, jpegData);
  }
  return imagePath;
}