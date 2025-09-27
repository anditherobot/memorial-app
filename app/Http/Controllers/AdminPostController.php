<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class AdminPostController extends Controller
{
    public function index()
    {
        $posts = Post::orderByDesc('created_at')->paginate(10);
        return view('updates.admin.index', compact('posts'));
    }

    public function create()
    {
        return view('updates.admin.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'body' => ['required','string'],
            'is_published' => ['nullable','boolean'],
            'published_at' => ['nullable','date'],
            'image' => ['nullable','file','max:51200','mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml'],
        ]);

        $post = new Post();
        $post->title = $data['title'];
        $post->body = $data['body'];
        $post->is_published = (bool)($data['is_published'] ?? false);
        $post->published_at = $data['published_at'] ?? ($post->is_published ? now() : null);
        $post->author_name = auth()->user()->name ?? 'Admin';
        $post->save();

        if (!empty($data['image'])) {
            $this->attachImage($post, $data['image']);
        }

        return redirect()->route('admin.updates.index')->with('status', 'Post created.');
    }

    public function edit(Post $post)
    {
        // Eager-load first media
        $cover = $post->media()->first();
        return view('updates.admin.edit', compact('post','cover'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'body' => ['required','string'],
            'is_published' => ['nullable','boolean'],
            'published_at' => ['nullable','date'],
            'image' => ['nullable','file','max:51200','mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml'],
            'remove_image' => ['nullable','boolean'],
        ]);

        $post->title = $data['title'];
        $post->body = $data['body'];
        $post->is_published = (bool)($data['is_published'] ?? false);
        $post->published_at = $data['published_at'] ?? ($post->is_published ? ($post->published_at ?? now()) : null);
        $post->save();

        if (!empty($data['remove_image'])) {
            $post->media()->detach();
        }

        if (!empty($data['image'])) {
            $post->media()->detach();
            $this->attachImage($post, $data['image']);
        }

        return redirect()->route('admin.updates.index')->with('status', 'Post updated.');
    }

    public function destroy(Post $post)
    {
        $post->media()->detach();
        $post->delete();
        return back()->with('status', 'Post deleted.');
    }

    protected function attachImage(Post $post, $file): void
    {
        $path = $file->storeAs('media/originals', Str::uuid()->toString().'_'.$file->getClientOriginalName(), 'public');
        $mime = $file->getMimeType();
        $width = null; $height = null;
        try {
            $manager = new ImageManager(new GdDriver());
            $image = $manager->read($file->getRealPath());
            $width = $image->width();
            $height = $image->height();
        } catch (\Throwable $e) {}

        $media = Media::create([
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $mime,
            'size_bytes' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'duration_seconds' => null,
            'hash' => hash_file('sha256', $file->getRealPath()),
            'storage_path' => $path,
        ]);

        // Thumbnail
        try {
            $manager = new ImageManager(new GdDriver());
            $img2 = $manager->read($file->getRealPath());
            $img2 = $img2->scale(width: 800, height: null);
            $thumbPath = 'media/derivatives/'.$media->id.'/thumb.jpg';
            Storage::disk('public')->put($thumbPath, (string) $img2->toJpeg(quality: 80));

            MediaDerivative::updateOrCreate(
                ['media_id' => $media->id, 'type' => 'thumbnail', 'storage_path' => $thumbPath],
                ['width' => $img2->width(), 'height' => $img2->height(), 'size_bytes' => Storage::disk('public')->size($thumbPath)]
            );
        } catch (\Throwable $e) {}

        $post->media()->attach($media->id, ['role' => 'cover', 'sort_order' => 0]);
    }
}

