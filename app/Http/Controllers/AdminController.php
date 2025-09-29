<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;

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

        // Recent uploads (media)
        $recentUploads = Media::latest()
            ->take(5)
            ->get();

        // Recent pictures with images
        $recentPictures = Media::whereIn('mime_type', ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->latest()
            ->take(6)
            ->get();

        // Recent posts/updates
        $recentPosts = Post::latest()
            ->take(5)
            ->get();

        // Current user
        $user = Auth::user();

        return view('admin.dashboard', compact('stats', 'recentUploads', 'recentPictures', 'recentPosts', 'user'));
    }
}

