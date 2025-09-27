<?php

namespace App\Http\Controllers;

use App\Models\Media;

class GalleryController extends Controller
{
    public function index()
    {
        $images = Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->latest()
            ->paginate(24);

        // Fallback samples when no media exist yet
        $samples = [];
        if ($images->count() === 0) {
            $samples = collect(range(1, 12))
                ->map(fn ($i) => "images/gallery/sample{$i}.svg")
                ->all();
        }

        return view('gallery.index', [
            'images' => $images,
            'samples' => $samples,
        ]);
    }
}
