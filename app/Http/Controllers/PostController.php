<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(5);

        if (request()->headers->get('HX-Request')) {
            return response()->view('updates._items', compact('posts'));
        }
        return view('updates.index', compact('posts'));
    }

    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);
        return view('updates.show', compact('post'));
    }
}
