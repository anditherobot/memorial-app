<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\Wish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_is_approved_is_false(): void
    {
        $wish = Wish::create([
            'name' => 'Guest',
            'message' => 'Hello',
            'submitted_ip' => '127.0.0.1',
        ]);
        $this->assertDatabaseHas('wishes', [
            'id' => $wish->id,
            'is_approved' => 0,
        ]);
    }

    public function test_can_approve_wish(): void
    {
        $wish = Wish::factory()->create(['is_approved' => false]);
        $wish->update(['is_approved' => true]);
        $this->assertTrue((bool) $wish->fresh()->is_approved);
    }

    public function test_morph_to_many_media_relationship(): void
    {
        $wish = Wish::factory()->create();
        $media = Media::factory()->create();

        $wish->media()->attach($media, ['role' => 'attachment', 'sort_order' => 1]);

        $this->assertCount(1, $wish->media);
        $this->assertEquals('attachment', $wish->media->first()->pivot->role);
        $this->assertEquals(1, $wish->media->first()->pivot->sort_order);
    }
}
