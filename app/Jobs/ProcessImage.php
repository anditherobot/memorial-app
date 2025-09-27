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

class ProcessImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = Media::find($this->mediaId);
        if (!$media || !str_starts_with($media->mime_type, 'image/')) return;

        $source = storage_path('app/public/'.$media->storage_path);
        $targetDir = 'media/derivatives/'.$media->id;
        $thumbPath = $targetDir.'/thumb.jpg';

        $manager = new ImageManager(new GdDriver());
        $image = $manager->read($source);
        $image = $image->scale(width: 800, height: null);

        \Storage::disk('public')->put($thumbPath, (string) $image->toJpeg(quality: 80));

        MediaDerivative::updateOrCreate(
            ['media_id' => $media->id, 'type' => 'thumbnail', 'storage_path' => $thumbPath],
            ['width' => $image->width(), 'height' => $image->height(), 'size_bytes' => \Storage::disk('public')->size($thumbPath)]
        );

        // Optional: optimize with spatie/image-optimizer if binaries are available.
        try {
            if (class_exists(\Spatie\ImageOptimizer\OptimizerChainFactory::class)) {
                $optimizer = \Spatie\ImageOptimizer\OptimizerChainFactory::create();
                $optimizer->optimize(storage_path('app/public/'.$thumbPath));
        }
        } catch (\Throwable $e) {
            // ignore optimization errors
        }
    }
}
