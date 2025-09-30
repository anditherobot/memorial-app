<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MediaDerivative;
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
            'hash',
            'storage_path',
            'is_public',
        ];

        $media = new Media();
        $this->assertEquals($fillable, $media->getFillable());
    }

    public function test_is_public_is_cast_to_boolean(): void
    {
        $media = Media::factory()->create(['is_public' => true]);

        $this->assertIsBool($media->is_public);
        $this->assertTrue($media->is_public);

        $media = Media::factory()->create(['is_public' => false]);
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

    public function test_media_factory_creates_image_attributes(): void
    {
        $media = Media::factory()->create();

        $this->assertNotNull($media->original_filename);
        $this->assertNotNull($media->mime_type);
        $this->assertNotNull($media->size_bytes);
        $this->assertNotNull($media->hash);
        $this->assertNotNull($media->storage_path);
        $this->assertIsBool($media->is_public);

        // For images, width and height should be set
        if (str_starts_with($media->mime_type, 'image/')) {
            $this->assertNotNull($media->width);
            $this->assertNotNull($media->height);
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