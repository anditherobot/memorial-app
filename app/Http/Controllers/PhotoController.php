<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkUploadRequest;
use App\Jobs\ProcessImageOptimization;
use App\Models\Media;
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
                $media = Media::create([
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size_bytes' => $file->getSize(),
                    'storage_path' => $file->store('media/' . now()->format('Y/m'), 's3_private'),
                    'is_public' => false,
                ]);

                $photo = Photo::create([
                    'user_id' => auth()->id(),
                    'media_id' => $media->id,
                ]);

                ProcessImageOptimization::dispatch($media);
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

        $thumbUrl = null;
        if ($photo->status === 'ready' && $photo->media) {
            $thumbnailDerivative = $photo->media->derivatives()->where('type', 'thumbnail')->first();
            if ($thumbnailDerivative) {
                $thumbUrl = Storage::disk($thumbnailDerivative->disk)->url($thumbnailDerivative->storage_path);
            }
        }

        return response()->json([
            'status' => $photo->status,
            'error' => $photo->error_message,
            'thumb_url' => $thumbUrl,
        ]);
    }
}
