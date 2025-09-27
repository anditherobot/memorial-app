<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_shows_samples_when_empty(): void
    {
        $res = $this->get('/gallery');
        $res->assertOk();
        $res->assertSee('images/gallery/sample1.svg');
    }

    public function test_gallery_lists_images_with_lightbox_and_pagination(): void
    {
        Storage::fake('public');

        Media::factory()->count(30)->create([
            'mime_type' => 'image/jpeg',
            'width' => 800,
            'height' => 600,
            'is_public' => true,
        ]);

        $res = $this->get('/gallery');
        $res->assertOk();
        $res->assertDontSee('Sample gallery');

        // Expect pagination present (next page link)
        $res->assertSee('page=2');
    }

    public function test_gallery_uses_thumbnail_if_available(): void
    {
        Storage::fake('public');

        $m = Media::factory()->create([
            'mime_type' => 'image/jpeg',
            'width' => 800,
            'height' => 600,
            'is_public' => true,
            'storage_path' => 'media/originals/x.jpg'
        ]);
        Storage::disk('public')->put('media/originals/x.jpg', 'orig');
        MediaDerivative::create([
            'media_id' => $m->id,
            'type' => 'thumbnail',
            'storage_path' => 'media/derivatives/'.$m->id.'/thumb.jpg',
            'width' => 400,
            'height' => 300,
            'size_bytes' => 10,
        ]);
        Storage::disk('public')->put('media/derivatives/'.$m->id.'/thumb.jpg', 'thumb');

        $html = $this->get('/gallery')->getContent();
        $this->assertStringContainsString('media/derivatives/'.$m->id.'/thumb.jpg', $html);
    }
}
