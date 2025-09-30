<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUploadRequest;
use App\Jobs\ProcessUploadedImage;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function create()
    {
        return view('photos.create');
    }

    public function store(BulkUploadRequest $request)
    {
        try {
            $uuids = [];
            foreach ($request->file('images') as $file) {
                $path = $file->store('photos/' . now()->format('Y/m'), 'local');
                $photo = Photo::create([
                    'user_id' => auth()->id(),
                    'original_path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
                ProcessUploadedImage::dispatch($photo);
                $uuids[] = $photo->uuid;
            }

            return response()->json(['uuids' => $uuids]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function status(Photo $photo)
    {
        // Ensure user can only check their own photos
        if ($photo->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json([
            'status' => $photo->status,
            'error' => $photo->error_message,
            'thumb_url' => $photo->status === 'ready' ? route('photos.thumb', $photo) : null,
        ]);
    }

    public function thumb(Photo $photo)
    {
        // Ensure user can only view their own photos
        if ($photo->user_id !== auth()->id()) {
            abort(403);
        }

        $path = $photo->variants['thumb'] ?? $photo->display_path ?? $photo->original_path;

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::response($path)
            ->header('Cache-Control', 'public, max-age=2592000, immutable');
    }
}
