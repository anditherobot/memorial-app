<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImage;
use App\Jobs\GeneratePoster;
use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager; // core v3
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,video/mp4,video/quicktime'],
        ]);

        $file = $validated['file'];
        $path = $file->storeAs('media/originals', Str::uuid()->toString().'_'.$file->getClientOriginalName(), 'public');

        $mime = $file->getMimeType();
        $width = null; $height = null; $duration = null;

        if (str_starts_with($mime, 'image/')) {
            try {
                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($file->getRealPath());
                $width = $image->width();
                $height = $image->height();
            } catch (\Throwable $e) {
                // ignore; keep nulls
            }
        }

        $media = Media::create([
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'duration_seconds' => $duration,
            'hash' => hash_file('sha256', $file->getRealPath()),
            'storage_path' => $path,
        ]);

        if (str_starts_with($mime, 'image/')) {
            ProcessImage::dispatch($media->id);
        } else {
            GeneratePoster::dispatch($media->id);
        }

        return response()->json([
            'id' => $media->id,
            'path' => $path,
            'mime' => $mime,
        ]);
    }

    public function show()
    {
        return view('upload.index');
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,video/mp4,video/quicktime'],
        ]);

        $file = $validated['file'];
        $path = $file->storeAs('media/originals', Str::uuid()->toString().'_'.$file->getClientOriginalName(), 'public');

        $mime = $file->getMimeType();
        $width = null; $height = null; $duration = null;

        if (str_starts_with($mime, 'image/')) {
            try {
                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($file->getRealPath());
                $width = $image->width();
                $height = $image->height();
            } catch (\Throwable $e) {
                // ignore; keep nulls
            }
        }

        $media = Media::create([
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'duration_seconds' => $duration,
            'hash' => hash_file('sha256', $file->getRealPath()),
            'storage_path' => $path,
            'is_public' => true, // Mark as public upload
        ]);

        // Generate thumbnail for images
        if (str_starts_with($mime, 'image/')) {
            try {
                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($file->getRealPath());
                $image = $image->scale(width: 800, height: null);
                $thumbPath = 'media/derivatives/'.$media->id.'/thumb.jpg';

                // Ensure directory exists
                $thumbDir = dirname(Storage::disk('public')->path($thumbPath));
                if (!is_dir($thumbDir)) {
                    mkdir($thumbDir, 0755, true);
                }

                Storage::disk('public')->put($thumbPath, (string) $image->toJpeg(quality: 80));

                MediaDerivative::updateOrCreate(
                    ['media_id' => $media->id, 'type' => 'thumbnail', 'storage_path' => $thumbPath],
                    ['width' => $image->width(), 'height' => $image->height(), 'size_bytes' => Storage::disk('public')->size($thumbPath)]
                );
            } catch (\Throwable $e) {
                // silently ignore
            }
        }

        return redirect()->route('upload.show')->with('status', 'Photo uploaded successfully! Thank you for sharing your memory.');
    }
}
