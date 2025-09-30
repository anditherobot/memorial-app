<?php

namespace Tests\Feature;

use App\Jobs\ProcessImageOptimization;
use App\Models\Media;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_upload_a_photo_and_media_is_created(): void
    {
        Storage::fake('s3_private');
        Storage::fake('s3_public');
        Queue::fake();

        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('photo.jpg', 1000, 800);

        $response = $this->actingAs($user)->withHeaders([
            'X-CSRF-TOKEN' => csrf_token(),
        ])->post(route('photos.store'), [
            'images' => [$file],
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['uuids']);

        $this->assertDatabaseCount('media', 1);
        $media = Media::first();

        $this->assertDatabaseHas('photos', [
            'user_id' => $user->id,
            'media_id' => $media->id,
        ]);

        Storage::disk('s3_private')->assertExists($media->storage_path);

        ProcessImageOptimization::assertDispatched(function ($job) use ($media) {
            return $job->media->is($media);
        });
    }
}
