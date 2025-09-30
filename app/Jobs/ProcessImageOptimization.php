<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class ProcessImageOptimization implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Media $media) {}

    public function handle(): void
    {
        if (!str_starts_with($this->media->mime_type, 'image/')) {
            \Log::warning('ProcessImageOptimization: Not an image', ['media_id' => $this->media->id, 'mime_type' => $this->media->mime_type]);
            return;
        }

        try {
            $manager = new ImageManager(new GdDriver());

            // Read from local public disk
            $originalPath = Storage::disk('public')->path($this->media->storage_path);

            if (!file_exists($originalPath)) {
                \Log::error('ProcessImageOptimization: Original file not found', [
                    'media_id' => $this->media->id,
                    'path' => $originalPath
                ]);
                return;
            }

            $image = $manager->read($originalPath);

            // Ensure derivatives directory exists
            Storage::disk('public')->makeDirectory('media/derivatives/' . $this->media->id);

            // 1. Thumbnail derivative (800px width for grid display)
            // Target: Under 200KB
            $thumbnailImage = clone $image;
            $thumbnailImage->scale(width: 800);
            $thumbnailPath = 'media/derivatives/' . $this->media->id . '/thumb.jpg';

            // Try different quality levels to stay under 200KB
            $thumbnailQuality = 80;
            $targetSize = 204800; // 200KB

            do {
                $thumbnailData = (string) $thumbnailImage->toJpeg(quality: $thumbnailQuality);
                $thumbnailSize = strlen($thumbnailData);

                if ($thumbnailSize <= $targetSize || $thumbnailQuality <= 50) {
                    Storage::disk('public')->put($thumbnailPath, $thumbnailData);
                    break;
                }

                $thumbnailQuality -= 5;
            } while ($thumbnailQuality >= 50);

            MediaDerivative::updateOrCreate(
                ['media_id' => $this->media->id, 'type' => 'thumbnail'],
                [
                    'storage_path' => $thumbnailPath,
                    'disk' => 'public',
                    'mime_type' => 'image/jpeg',
                    'width' => $thumbnailImage->width(),
                    'height' => $thumbnailImage->height(),
                    'size_bytes' => Storage::disk('public')->size($thumbnailPath),
                ]
            );

            // 2. Web-optimized derivative (1920px max width for full view/lightbox)
            // Target: Under 2MB
            $webOptimizedImage = clone $image;
            if ($webOptimizedImage->width() > 1920) {
                $webOptimizedImage->scale(width: 1920);
            }
            $webOptimizedPath = 'media/derivatives/' . $this->media->id . '/web-optimized.jpg';

            // Try different quality levels to stay under 2MB
            $webQuality = 85;
            $maxSize = 2097152; // 2MB

            do {
                $webData = (string) $webOptimizedImage->toJpeg(quality: $webQuality);
                $webSize = strlen($webData);

                if ($webSize <= $maxSize || $webQuality <= 60) {
                    Storage::disk('public')->put($webOptimizedPath, $webData);
                    break;
                }

                $webQuality -= 5;
            } while ($webQuality >= 60);

            MediaDerivative::updateOrCreate(
                ['media_id' => $this->media->id, 'type' => 'web-optimized'],
                [
                    'storage_path' => $webOptimizedPath,
                    'disk' => 'public',
                    'mime_type' => 'image/jpeg',
                    'width' => $webOptimizedImage->width(),
                    'height' => $webOptimizedImage->height(),
                    'size_bytes' => Storage::disk('public')->size($webOptimizedPath),
                ]
            );

            \Log::info('ProcessImageOptimization: Completed successfully', [
                'media_id' => $this->media->id,
                'derivatives' => ['thumbnail', 'web-optimized']
            ]);

        } catch (\Throwable $e) {
            \Log::error('ProcessImageOptimization: Failed', [
                'media_id' => $this->media->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
