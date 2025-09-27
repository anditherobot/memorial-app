<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:generate {--force : Regenerate existing thumbnails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate thumbnails for media files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');

        $query = Media::where('mime_type', 'like', 'image/%');

        if (!$force) {
            $query->whereDoesntHave('derivatives', function ($q) {
                $q->where('type', 'thumbnail');
            });
        }

        $media = $query->get();

        $this->info("Found {$media->count()} images to process.");

        $manager = new ImageManager(new GdDriver());

        foreach ($media as $item) {
            $this->info("Processing: {$item->original_filename}");

            try {
                $fullPath = Storage::disk('public')->path($item->storage_path);

                if (!file_exists($fullPath)) {
                    $this->error("File not found: {$fullPath}");
                    continue;
                }

                $image = $manager->read($fullPath);
                $image = $image->scale(width: 800, height: null);
                $thumbPath = 'media/derivatives/'.$item->id.'/thumb.jpg';

                // Ensure directory exists
                $thumbDir = dirname(Storage::disk('public')->path($thumbPath));
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0755, true);
                }

                Storage::disk('public')->put($thumbPath, (string) $image->toJpeg(quality: 80));

                MediaDerivative::updateOrCreate(
                    ['media_id' => $item->id, 'type' => 'thumbnail', 'storage_path' => $thumbPath],
                    ['width' => $image->width(), 'height' => $image->height(), 'size_bytes' => Storage::disk('public')->size($thumbPath)]
                );

                $this->info("✓ Generated thumbnail for {$item->original_filename}");

            } catch (\Throwable $e) {
                $this->error("✗ Failed to generate thumbnail for {$item->original_filename}: {$e->getMessage()}");
            }
        }

        $this->info('Thumbnail generation complete!');
    }
}
