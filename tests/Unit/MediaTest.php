<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MediaDerivative;
use App\Models\Post;
use App\Models\Wish;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_media_has_fillable_attributes(): void
    {
        $fillable = [
            'original_filename',
            'mime_type',
            'size_bytes',
            'width',
            'height',
            'duration_seconds',
            'hash',
            'storage_path',
            'is_public',
        ];

        $media = new Media();
        $this->assertEquals($fillable, $media->getFillable());
    }

    public function test_is_public_is_cast_to_boolean(): void
    {
        $media = Media::factory()->create(['is_public' => 1]);

        $this->assertIsBool($media->is_public);
        $this->assertTrue($media->is_public);

        $media = Media::factory()->create(['is_public' => 0]);
        $this->assertIsBool($media->is_public);
        $this->assertFalse($media->is_public);
    }

    public function test_media_has_derivatives_relationship(): void
    {
        $media = Media::factory()->create();
        $derivatives = MediaDerivative::factory()->count(3)->create(['media_id' => $media->id]);

        $this->assertInstanceOf(Collection::class, $media->derivatives);
        $this->assertCount(3, $media->derivatives);
        $this->assertTrue($media->derivatives->contains($derivatives[0]));
    }

    public function test_media_has_posts_relationship(): void
    {
        $media = Media::factory()->create();
        $post = Post::factory()->create();

        $media->posts()->attach($post, ['role' => 'featured', 'sort_order' => 1]);

        $this->assertInstanceOf(Collection::class, $media->posts);
        $this->assertCount(1, $media->posts);
        $this->assertTrue($media->posts->contains($post));
        $this->assertEquals('featured', $media->posts->first()->pivot->role);
        $this->assertEquals(1, $media->posts->first()->pivot->sort_order);
    }

    public function test_media_has_wishes_relationship(): void
    {
        $media = Media::factory()->create();
        $wish = Wish::factory()->create();

        $media->wishes()->attach($wish, ['role' => 'attachment', 'sort_order' => 1]);

        $this->assertInstanceOf(Collection::class, $media->wishes);
        $this->assertCount(1, $media->wishes);
        $this->assertTrue($media->wishes->contains($wish));
        $this->assertEquals('attachment', $media->wishes->first()->pivot->role);
        $this->assertEquals(1, $media->wishes->first()->pivot->sort_order);
    }

    public function test_media_factory_creates_image_attributes(): void
    {
        $media = Media::factory()->create();

        $this->assertNotNull($media->original_filename);
        $this->assertNotNull($media->mime_type);
        $this->assertNotNull($media->size_bytes);
        $this->assertNotNull($media->hash);
        $this->assertNotNull($media->storage_path);
        $this->assertIsBool($media->is_public);

        // For images, width and height should be set, duration should be null
        if (str_starts_with($media->mime_type, 'image/')) {
            $this->assertNotNull($media->width);
            $this->assertNotNull($media->height);
            $this->assertNull($media->duration_seconds);
        }

        // For videos, duration should be set
        if (str_starts_with($media->mime_type, 'video/')) {
            $this->assertNotNull($media->duration_seconds);
        }
    }

    public function test_media_hash_is_unique(): void
    {
        $media1 = Media::factory()->create();
        $media2 = Media::factory()->create();

        $this->assertNotEquals($media1->hash, $media2->hash);
    }

    public function test_media_filename_extension_matches_mime_type(): void
    {
        $media = Media::factory()->create();

        if ($media->mime_type === 'image/jpeg') {
            $this->assertStringEndsWith('.jpg', $media->original_filename);
        }

        if ($media->mime_type === 'image/png') {
            $this->assertStringEndsWith('.png', $media->original_filename);
        }

        if ($media->mime_type === 'video/mp4') {
            $this->assertStringEndsWith('.mp4', $media->original_filename);
        }
    }

    public function test_media_size_bytes_is_positive(): void
    {
        $media = Media::factory()->create();

        $this->assertGreaterThan(0, $media->size_bytes);
    }

    public function test_media_dimensions_are_valid_for_images(): void
    {
        $media = Media::factory()->create();

        if (str_starts_with($media->mime_type, 'image/')) {
            $this->assertGreaterThan(0, $media->width);
            $this->assertGreaterThan(0, $media->height);
        }
    }
}