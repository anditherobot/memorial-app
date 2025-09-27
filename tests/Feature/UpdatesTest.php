<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatesTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_index_shows_only_published(): void
    {
        Post::factory()->create(['title' => 'Visible', 'is_published' => true, 'published_at' => now()]);
        Post::factory()->create(['title' => 'Hidden', 'is_published' => false, 'published_at' => null]);

        $res = $this->get('/updates');
        $res->assertOk();
        $res->assertSee('Visible');
        $res->assertDontSee('Hidden');
    }

    public function test_updates_show_404_for_unpublished(): void
    {
        $draft = Post::factory()->create(['is_published' => false]);
        $this->get(route('updates.show', $draft))->assertNotFound();
    }

    public function test_htmx_load_more_returns_partial(): void
    {
        Post::factory()->count(7)->sequence(...collect(range(1,7))->map(fn($i)=>['title'=>"P$i",'is_published'=>true,'published_at'=>now()->subDays($i)])->all())->create();

        $first = $this->get('/updates');
        $first->assertOk();
        $first->assertSee('P1');

        // Ask for next page with HX-Request header and ensure partial returns
        $res = $this->withHeader('HX-Request','true')->get('/updates?page=2');
        $res->assertOk();
        $res->assertSee('P6');
        // Ensure no full layout markers
        $res->assertDontSee('<html');
    }
}

