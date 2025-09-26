<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Models\Post;
use App\Models\Media;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'wishes_pending' => Wish::where('is_approved', false)->count(),
            'wishes_total' => Wish::count(),
            'posts_total' => Post::count(),
            'media_total' => Media::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}

