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

        return view('gallery.index', compact('images'));
    }
}

