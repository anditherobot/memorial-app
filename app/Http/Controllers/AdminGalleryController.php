<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImageOptimization;
use App\Models\Media;
use App\Models\MediaDerivative;
use App\Models\Photo;
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

        $photos = Photo::query()
            ->where('status', 'completed')
            ->latest()
            ->get();

        return view('admin.gallery', compact('images', 'photos'));
    }

    public function upload(Request $request)
    {
        // Support both 'photos' and 'file' parameter names
        $inputName = $request->has('photos') ? 'photos' : 'file';

        $validated = $request->validate([
            $inputName => ['required', 'array'],
            $inputName.'.*' => ['required', 'file', 'max:10240', 'mimes:jpeg,jpg,png,gif,webp,heic'],
        ], [
            $inputName.'.required' => 'Please select at least one file to upload.',
            $inputName.'.*.max' => 'Each file must not exceed 10MB.',
            $inputName.'.*.mimes' => 'Only image files (JPEG, PNG, GIF, WEBP, HEIC) are allowed.',
        ]);

        $uploadedCount = 0;
        $failedFiles = [];

        foreach ($validated[$inputName] as $file) {
            try {
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
                        \Log::warning('Failed to read image dimensions: ' . $e->getMessage(), [
                            'file' => $file->getClientOriginalName()
                        ]);
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

                // Dispatch job to generate derivatives
                if (str_starts_with($mime, 'image/')) {
                    ProcessImageOptimization::dispatch($media);
                }

                $uploadedCount++;
            } catch (\Throwable $e) {
                \Log::error('Failed to upload file: ' . $e->getMessage(), [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
                $failedFiles[] = $file->getClientOriginalName();
            }
        }

        // Determine the appropriate message and type
        if ($uploadedCount > 0 && count($failedFiles) === 0) {
            $message = $uploadedCount === 1 ? 'Image uploaded successfully!' : "Successfully uploaded {$uploadedCount} images!";
            return redirect()->route('admin.gallery')->with('status', $message);
        } elseif ($uploadedCount > 0 && count($failedFiles) > 0) {
            $message = "Uploaded {$uploadedCount} image(s). Failed: " . implode(', ', $failedFiles);
            return redirect()->route('admin.gallery')->with('warning', $message);
        } else {
            $message = 'All uploads failed. Failed files: ' . implode(', ', $failedFiles);
            return redirect()->route('admin.gallery')->with('error', $message);
        }
    }

    public function optimize(Request $request)
    {
        $validated = $request->validate([
            'media_ids' => ['required', 'array'],
            'media_ids.*' => ['required', 'integer', 'exists:media,id'],
        ]);

        $dispatchedCount = 0;
        foreach ($mediaIds as $mediaId) {
            $media = Media::find($mediaId);

            if (!$media || !str_starts_with($media->mime_type, 'image/')) {
                continue;
            }

            // Skip if already optimized
            if ($media->derivatives()->where('type', 'web-optimized')->exists()) {
                continue;
            }

            ProcessImageOptimization::dispatch($media);
            $dispatchedCount++;
        }

        $message = "Optimization job dispatched for {$dispatchedCount} image(s). They will be processed in the background.";

        return redirect()->route('admin.gallery')->with('status', $message);
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

