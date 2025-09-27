<?php

namespace Tests\Unit;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_and_defaults(): void
    {
        // Omit is_published so DB default applies
        $post = Post::query()->create([
            'title' => 'Hello',
            'body' => '<p>Body</p>',
            // omit is_published to use DB default
            'published_at' => null,
            'author_name' => 'Tester',
        ]);

        $post->refresh();
        $this->assertIsBool($post->is_published);
        $this->assertTrue($post->is_published);
        $this->assertNull($post->published_at);
    }

    public function test_mark_unpublished_and_publish_later(): void
    {
        $post = Post::factory()->create(['is_published' => false, 'published_at' => null]);
        $this->assertFalse($post->fresh()->is_published);

        $post->update(['is_published' => true, 'published_at' => now()]);
        $post->refresh();
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }
}
