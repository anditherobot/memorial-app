<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class AdminGalleryController extends Controller
{
    public function index(Request $request)
    {
        $images = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->paginate(24);

        return view('admin.gallery', compact('images'));
    }

    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml'],
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
            'is_public' => false, // Admin uploads are not public by default
        ]);

        // Generate a thumbnail for images
        if (str_starts_with($mime, 'image/')) {
            try {
                $manager = new ImageManager(new GdDriver());
                $image = $manager->read($file->getRealPath());
                $image = $image->scale(width: 800, height: null);
                $thumbPath = 'media/derivatives/'.$media->id.'/thumb.jpg';
                Storage::disk('public')->put($thumbPath, (string) $image->toJpeg(quality: 80));

                MediaDerivative::updateOrCreate(
                    ['media_id' => $media->id, 'type' => 'thumbnail', 'storage_path' => $thumbPath],
                    ['width' => $image->width(), 'height' => $image->height(), 'size_bytes' => Storage::disk('public')->size($thumbPath)]
                );
            } catch (\Throwable $e) {
                // silently ignore
            }
        }

        return redirect()->route('admin.gallery')->with('status', 'Image uploaded.');
    }

    public function destroy(Media $media)
    {
        // Delete derivative files
        foreach ($media->derivatives as $deriv) {
            Storage::disk('public')->delete($deriv->storage_path);
            $deriv->delete();
        }
        // Delete original file
        Storage::disk('public')->delete($media->storage_path);
        $media->delete();

        return back()->with('status', 'Image deleted.');
    }
}

