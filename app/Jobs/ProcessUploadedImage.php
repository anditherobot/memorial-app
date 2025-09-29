<?php

namespace App\Jobs;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessUploadedImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $photo;

    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    public function handle(): void
    {
        $this->photo->update(['status' => 'processing']);

        try {
            $image = Image::make(Storage::disk('local')->get($this->photo->original_path));

            // Strip EXIF data
            $image->orientate();
            $image->strip();

            // Create display image
            $image->resize(2560, 2560, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $displayPath = 'photos/' . now()->format('Y/m') . '/' . $this->photo->uuid . '.webp';
            Storage::disk('local')->put($displayPath, (string) $image->encode('webp', 80));

            // Create variants
            $variants = [];
            $thumb = clone $image;
            $thumb->resize(400, 400, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $thumbPath = 'photos/' . now()->format('Y/m') . '/' . $this->photo->uuid . '_thumb.webp';
            Storage::disk('local')->put($thumbPath, (string) $thumb->encode('webp', 80));
            $variants['thumb'] = $thumbPath;

            $md = clone $image;
            $md->resize(1024, 1024, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $mdPath = 'photos/' . now()->format('Y/m') . '/' . $this->photo->uuid . '_md.webp';
            Storage::disk('local')->put($mdPath, (string) $md->encode('webp', 80));
            $variants['md'] = $mdPath;

            $this->photo->update([
                'display_path' => $displayPath,
                'variants' => $variants,
                'width' => $image->width(),
                'height' => $image->height(),
                'status' => 'ready',
            ]);
        } catch (\Exception $e) {
            $this->photo->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}