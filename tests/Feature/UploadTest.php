<?php

namespace Tests\Feature;

use App\Jobs\GeneratePoster;
use App\Jobs\ProcessImage;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_json_upload_endpoint_accepts_image_and_dispatches_processing(): void
    {
        Storage::fake('public');
        Bus::fake();

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $res = $this->postJson('/uploads', [
            'file' => $file,
        ]);

        $res->assertOk();

        $this->assertDatabaseCount('media', 1);
        $media = Media::first();
        $this->assertStringStartsWith('image/', $media->mime_type);

        Bus::assertDispatched(ProcessImage::class);
    }

    public function test_json_upload_endpoint_rejects_large_files(): void
    {
        Storage::fake('public');
        // Create a fake file slightly over 50MB (limit is 51200 KB)
        $file = UploadedFile::fake()->create('big.jpg', 52000, 'image/jpeg');

        $this->postJson('/uploads', ['file' => $file])
            ->assertStatus(422);
    }
}

