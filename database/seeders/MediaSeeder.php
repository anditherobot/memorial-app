<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $samples = range(1, 12);

        foreach ($samples as $i) {
            $src = public_path("images/gallery/sample{$i}.svg");
            if (!File::exists($src)) {
                continue;
            }

            $relPath = "gallery/sample{$i}.svg";
            $thumbRel = "gallery/thumbs/sample{$i}.svg";

            // Ensure directories on storage disk
            Storage::disk('public')->makeDirectory('gallery');
            Storage::disk('public')->makeDirectory('gallery/thumbs');

            // Write original and thumbnail (using same SVG for demo)
            $content = File::get($src);
            Storage::disk('public')->put($relPath, $content);
            Storage::disk('public')->put($thumbRel, $content);

            // Ensure public/storage exists even without symlink (Windows dev)
            $publicStorageBase = public_path('storage');
            if (!is_link($publicStorageBase)) {
                File::ensureDirectoryExists(public_path('storage/gallery'));
                File::ensureDirectoryExists(public_path('storage/gallery/thumbs'));
                File::copy($src, public_path("storage/{$relPath}"));
                File::copy($src, public_path("storage/{$thumbRel}"));
            }

            $size = strlen($content);
            $hash = hash('sha256', $content);

            $media = Media::firstOrCreate(
                ['hash' => $hash],
                [
                    'original_filename' => "sample{$i}.svg",
                    'mime_type' => 'image/svg+xml',
                    'size_bytes' => $size,
                    'width' => 1200,
                    'height' => 800,
                    'storage_path' => $relPath,
                ]
            );

            MediaDerivative::firstOrCreate(
                [
                    'media_id' => $media->id,
                    'type' => 'thumbnail',
                    'storage_path' => $thumbRel,
                ],
                [
                    'width' => 480,
                    'height' => 320,
                    'size_bytes' => $size,
                ]
            );
        }
    }
}

