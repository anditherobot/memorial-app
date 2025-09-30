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
        if (!str_starts_with($this->media->mime_type, 'image/')) return;

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read(Storage::disk('s3_private')->get($this->media->storage_path));

        // Original derivative (private S3)
        MediaDerivative::updateOrCreate(
            ['media_id' => $this->media->id, 'type' => 'original'],
            [
                'storage_path' => $this->media->storage_path,
                'disk' => 's3_private',
                'mime_type' => $this->media->mime_type,
                'width' => $image->width(),
                'height' => $image->height(),
                'size_bytes' => Storage::disk('s3_private')->size($this->media->storage_path),
            ]
        );

        // Thumbnail derivative (public S3)
        $thumbnailImage = clone $image;
        $thumbnailImage->cover(150, 150);
        $thumbnailPath = 'media/' . $this->media->id . '/thumbnail.webp';
        Storage::disk('s3_public')->put($thumbnailPath, (string) $thumbnailImage->toWebp(quality: 80));

        MediaDerivative::updateOrCreate(
            ['media_id' => $this->media->id, 'type' => 'thumbnail'],
            [
                'storage_path' => $thumbnailPath,
                'disk' => 's3_public',
                'mime_type' => 'image/webp',
                'width' => $thumbnailImage->width(),
                'height' => $thumbnailImage->height(),
                'size_bytes' => Storage::disk('s3_public')->size($thumbnailPath),
            ]
        );

        // Medium derivative (public S3)
        $mediumImage = clone $image;
        $mediumImage->scale(width: 600);
        $mediumPath = 'media/' . $this->media->id . '/medium.webp';
        Storage::disk('s3_public')->put($mediumPath, (string) $mediumImage->toWebp(quality: 80));

        MediaDerivative::updateOrCreate(
            ['media_id' => $this->media->id, 'type' => 'medium'],
            [
                'storage_path' => $mediumPath,
                'disk' => 's3_public',
                'mime_type' => 'image/webp',
                'width' => $mediumImage->width(),
                'height' => $mediumImage->height(),
                'size_bytes' => Storage::disk('s3_public')->size($mediumPath),
            ]
        );

        // Large derivative (public S3)
        $largeImage = clone $image;
        $largeImage->scale(width: 1200);
        $largePath = 'media/' . $this->media->id . '/large.webp';
        Storage::disk('s3_public')->put($largePath, (string) $largeImage->toWebp(quality: 80));

        MediaDerivative::updateOrCreate(
            ['media_id' => $this->media->id, 'type' => 'large'],
            [
                'storage_path' => $largePath,
                'disk' => 's3_public',
                'mime_type' => 'image/webp',
                'width' => $largeImage->width(),
                'height' => $largeImage->height(),
                'size_bytes' => Storage::disk('s3_public')->size($largePath),
            ]
        );

        // Optional: optimize with spatie/image-optimizer if binaries are available.
        try {
            if (class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class)) {
                $optimizer = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
                $optimizer->optimize(Storage::disk('s3_public')->path($thumbnailPath));
                $optimizer->optimize(Storage::disk('s3_public')->path($mediumPath));
                $optimizer->optimize(Storage::disk('s3_public')->path($largePath));
            }
        } catch (\Throwable $e) {
            // ignore optimization errors
        }
    }
}
