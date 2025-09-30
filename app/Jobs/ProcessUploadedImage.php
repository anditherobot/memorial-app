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
    public $tries = 3;
    public $backoff = 10;
    public $timeout = 120;

    public function __construct(Photo $photo)
    {
        $this->photo = $photo;
    }

    public function handle(): void
    {
        $this->photo->update(['status' => 'processing']);

        try {
            // Check HEIC support if needed
            $isHeic = in_array($this->photo->mime_type, ['image/heic', 'image/heif']);
            if ($isHeic) {
                $heicOk = class_exists(\Imagick::class) &&
                    (in_array('HEIC', \Imagick::queryFormats('HEIC')) ||
                     in_array('HEIF', \Imagick::queryFormats('HEIF')));

                if (!$heicOk) {
                    $this->photo->update([
                        'status' => 'error',
                        'error_message' => 'HEIC/HEIF format not supported on this server. Please convert to JPEG or PNG.',
                    ]);
                    return;
                }
            }

            // Check for decompression bombs
            $imageData = Storage::disk('local')->get($this->photo->original_path);
            [$width, $height] = getimagesizefromstring($imageData) ?: [0, 0];
            if (($width * $height) > 80_000_000) { // 80MP limit
                $this->photo->update([
                    'status' => 'error',
                    'error_message' => 'Image dimensions too large (exceeds 80 megapixel limit).',
                ]);
                return;
            }

            $image = Image::make($imageData);

            // Strip EXIF data
            $image->orientate();
            $image->strip();

            // Create display image with WebP fallback
            $image->resize(2560, 2560, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            try {
                $displayEncoded = (string) $image->encode('webp', 80);
                $displayExt = 'webp';
            } catch (\Throwable $e) {
                // Fallback to progressive JPEG if WebP encoding fails
                $displayEncoded = (string) $image->encode('jpg', 82)->interlace();
                $displayExt = 'jpg';
            }

            $displayPath = 'photos/' . now()->format('Y/m') . '/' . $this->photo->uuid . '.' . $displayExt;
            Storage::disk('local')->put($displayPath, $displayEncoded);

            // Create variants
            $variants = [];

            // Thumbnail: square crop for consistent grid display
            $thumb = clone $image;
            $thumb->fit(400, 400);

            try {
                $thumbEncoded = (string) $thumb->encode('webp', 80);
                $thumbExt = 'webp';
            } catch (\Throwable $e) {
                $thumbEncoded = (string) $thumb->encode('jpg', 82)->interlace();
                $thumbExt = 'jpg';
            }

            $thumbPath = 'photos/' . now()->format('Y/m') . '/' . $this->photo->uuid . '_thumb.' . $thumbExt;
            Storage::disk('local')->put($thumbPath, $thumbEncoded);
            $variants['thumb'] = $thumbPath;

            // Medium: maintain aspect ratio
            $md = clone $image;
            $md->resize(1024, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            try {
                $mdEncoded = (string) $md->encode('webp', 80);
                $mdExt = 'webp';
            } catch (\Throwable $e) {
                $mdEncoded = (string) $md->encode('jpg', 82)->interlace();
                $mdExt = 'jpg';
            }

            $mdPath = 'photos/' . now()->format('Y/m') . '/' . $this->photo->uuid . '_md.' . $mdExt;
            Storage::disk('local')->put($mdPath, $mdEncoded);
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