<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImage;
use App\Jobs\GeneratePoster;
use App\Models\Media;
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
}
