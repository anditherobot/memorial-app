<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Create some media
        $media = Media::factory(10)->create();

        // Create posts and randomly attach media
        Post::factory(12)->create()->each(function (Post $post) use ($media) {
            $attach = $media->random(rand(0, 3));
            foreach ($attach as $idx => $m) {
                $post->media()->attach($m->id, [
                    'role' => 'gallery',
                    'sort_order' => $idx,
                ]);
            }
        });
    }
}

