<?php

namespace Tests\Unit;

use App\Jobs\ProcessImageOptimization;
use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessImageOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_job_creates_thumbnail_and_web_optimized_derivatives(): void
    {
        // Create a test image file
        $file = UploadedFile::fake()->image('test.jpg', 3000, 2000); // Large image
        $storagePath = 'media/originals/' . uniqid() . '_test.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        // Create media record
        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'width' => 3000,
            'height' => 2000,
        ]);

        // Run the job
        $job = new ProcessImageOptimization($media);
        $job->handle();

        // Assert derivatives were created in database
        $this->assertDatabaseHas('media_derivatives', [
            'media_id' => $media->id,
            'type' => 'thumbnail',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
        ]);

        $this->assertDatabaseHas('media_derivatives', [
            'media_id' => $media->id,
            'type' => 'web-optimized',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
        ]);

        // Assert files exist
        $thumbnail = $media->derivatives()->where('type', 'thumbnail')->first();
        $webOptimized = $media->derivatives()->where('type', 'web-optimized')->first();

        $this->assertNotNull($thumbnail);
        $this->assertNotNull($webOptimized);

        Storage::disk('public')->assertExists($thumbnail->storage_path);
        Storage::disk('public')->assertExists($webOptimized->storage_path);
    }

    public function test_thumbnail_meets_size_target_under_200kb(): void
    {
        // Create a large test image
        $file = UploadedFile::fake()->image('large.jpg', 4000, 3000);
        $storagePath = 'media/originals/' . uniqid() . '_large.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'width' => 4000,
            'height' => 3000,
        ]);

        // Run the job
        $job = new ProcessImageOptimization($media);
        $job->handle();

        // Check thumbnail size
        $thumbnail = $media->derivatives()->where('type', 'thumbnail')->first();
        $this->assertNotNull($thumbnail);

        // Thumbnail should be under 200KB (204800 bytes)
        $this->assertLessThan(204800, $thumbnail->size_bytes,
            "Thumbnail size {$thumbnail->size_bytes} bytes exceeds 200KB target");
    }

    public function test_web_optimized_meets_size_target_under_2mb(): void
    {
        // Create a very large test image
        $file = UploadedFile::fake()->image('huge.jpg', 5000, 4000);
        $storagePath = 'media/originals/' . uniqid() . '_huge.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'width' => 5000,
            'height' => 4000,
        ]);

        // Run the job
        $job = new ProcessImageOptimization($media);
        $job->handle();

        // Check web-optimized size
        $webOptimized = $media->derivatives()->where('type', 'web-optimized')->first();
        $this->assertNotNull($webOptimized);

        // Web-optimized should be under 2MB (2097152 bytes)
        $this->assertLessThan(2097152, $webOptimized->size_bytes,
            "Web-optimized size {$webOptimized->size_bytes} bytes exceeds 2MB target");
    }

    public function test_thumbnail_has_correct_dimensions(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 2000, 1500);
        $storagePath = 'media/originals/' . uniqid() . '_test.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'width' => 2000,
            'height' => 1500,
        ]);

        $job = new ProcessImageOptimization($media);
        $job->handle();

        $thumbnail = $media->derivatives()->where('type', 'thumbnail')->first();

        // Thumbnail should be 800px wide
        $this->assertEquals(800, $thumbnail->width);

        // Height should maintain aspect ratio (800/2000 * 1500 = 600)
        $this->assertEquals(600, $thumbnail->height);
    }

    public function test_web_optimized_respects_max_width(): void
    {
        $file = UploadedFile::fake()->image('test.jpg', 3000, 2000);
        $storagePath = 'media/originals/' . uniqid() . '_test.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'width' => 3000,
            'height' => 2000,
        ]);

        $job = new ProcessImageOptimization($media);
        $job->handle();

        $webOptimized = $media->derivatives()->where('type', 'web-optimized')->first();

        // Web-optimized should be max 1920px wide
        $this->assertLessThanOrEqual(1920, $webOptimized->width);

        // Should maintain aspect ratio (1920/3000 * 2000 = 1280)
        $this->assertEquals(1280, $webOptimized->height);
    }

    public function test_web_optimized_does_not_upscale_small_images(): void
    {
        // Image smaller than 1920px should not be upscaled
        $file = UploadedFile::fake()->image('small.jpg', 1000, 800);
        $storagePath = 'media/originals/' . uniqid() . '_small.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $file->getSize(),
            'width' => 1000,
            'height' => 800,
        ]);

        $job = new ProcessImageOptimization($media);
        $job->handle();

        $webOptimized = $media->derivatives()->where('type', 'web-optimized')->first();

        // Should keep original dimensions
        $this->assertEquals(1000, $webOptimized->width);
        $this->assertEquals(800, $webOptimized->height);
    }

    public function test_job_skips_non_image_files(): void
    {
        $media = Media::factory()->create([
            'mime_type' => 'application/pdf',
            'storage_path' => 'media/originals/document.pdf',
        ]);

        $job = new ProcessImageOptimization($media);
        $job->handle();

        // Should not create any derivatives
        $this->assertCount(0, $media->derivatives);
    }

    public function test_job_handles_missing_file_gracefully(): void
    {
        $media = Media::factory()->create([
            'storage_path' => 'media/originals/nonexistent.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $job = new ProcessImageOptimization($media);
        $job->handle();

        // Should not crash and should not create derivatives
        $this->assertCount(0, $media->derivatives);
    }

    public function test_derivatives_achieve_significant_size_reduction(): void
    {
        $file = UploadedFile::fake()->image('large.jpg', 4000, 3000);
        $storagePath = 'media/originals/' . uniqid() . '_large.jpg';
        Storage::disk('public')->put($storagePath, $file->get());

        $originalSize = $file->getSize();

        $media = Media::factory()->create([
            'storage_path' => $storagePath,
            'mime_type' => 'image/jpeg',
            'size_bytes' => $originalSize,
            'width' => 4000,
            'height' => 3000,
        ]);

        $job = new ProcessImageOptimization($media);
        $job->handle();

        $thumbnail = $media->derivatives()->where('type', 'thumbnail')->first();
        $webOptimized = $media->derivatives()->where('type', 'web-optimized')->first();

        // Thumbnail should be at least 90% smaller than original
        $thumbnailReduction = (1 - ($thumbnail->size_bytes / $originalSize)) * 100;
        $this->assertGreaterThan(90, $thumbnailReduction,
            "Thumbnail only reduced by {$thumbnailReduction}%, expected >90%");

        // Web-optimized should be at least 50% smaller than original
        $webOptimizedReduction = (1 - ($webOptimized->size_bytes / $originalSize)) * 100;
        $this->assertGreaterThan(50, $webOptimizedReduction,
            "Web-optimized only reduced by {$webOptimizedReduction}%, expected >50%");
    }
}
