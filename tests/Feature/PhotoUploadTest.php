<?php

namespace Tests\Feature;

use App\Jobs\ProcessUploadedImage;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_a_photo(): void
    {
        Storage::fake('local');
        Queue::fake();

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->withHeaders([
            'X-CSRF-TOKEN' => csrf_token(),
        ])->post(route('photos.store'), [
            'images' => [$file],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['uuids']);

        $this->assertDatabaseHas('photos', [
            'status' => 'pending',
        ]);

        Queue::assertPushed(ProcessUploadedImage::class);
    }

    public function test_image_is_processed_successfully(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('photo.jpg', 2000, 3000);
        $path = $file->store('photos/' . now()->format('Y/m'), 'local');
        $photo = Photo::factory()->make([
            'original_path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => $file->getSize(),
        ]);
        $photo->save();

        (new ProcessUploadedImage($photo))->handle();

        $this->assertDatabaseHas('photos', [
            'id' => $photo->id,
            'status' => 'ready',
            'width' => 2000,
            'height' => 3000,
        ]);

        Storage::disk('local')->assertExists($photo->fresh()->display_path);
        Storage::disk('local')->assertExists($photo->fresh()->variants['thumb']);
        Storage::disk('local')->assertExists($photo->fresh()->variants['md']);
    }
}
