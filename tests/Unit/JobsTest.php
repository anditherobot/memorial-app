<?php

namespace Tests\Unit;

use App\Jobs\GeneratePoster;
use App\Jobs\ProcessImage;
use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobsTest extends TestCase
{
    use RefreshDatabase;

    // 1x1 px PNG (valid minimal)
    private const PNG_1PX_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII=';

    public function test_process_image_generates_thumbnail_derivative(): void
    {
        // Use real public disk so job reads from storage_path('app/public/...')
        $path = 'media/originals/test.png';
        $bytes = base64_decode(self::PNG_1PX_BASE64);
        Storage::disk('public')->put($path, $bytes);

        $media = Media::create([
            'original_filename' => 'test.png',
            'mime_type' => 'image/png',
            'size_bytes' => strlen($bytes),
            'width' => 1,
            'height' => 1,
            'duration_seconds' => null,
            'hash' => hash('sha256', $bytes),
            'storage_path' => $path,
            'is_public' => true,
        ]);

        (new ProcessImage($media->id))->handle();

        $derivative = MediaDerivative::where('media_id', $media->id)
            ->where('type', 'thumbnail')
            ->first();

        $this->assertNotNull($derivative);
        $this->assertTrue(Storage::disk('public')->exists($derivative->storage_path));
    }

    public function test_generate_poster_creates_placeholder_for_video(): void
    {
        $media = Media::create([
            'original_filename' => 'clip.mp4',
            'mime_type' => 'video/mp4',
            'size_bytes' => 1024,
            'width' => null,
            'height' => null,
            'duration_seconds' => 10,
            'hash' => hash('sha256', 'clip'),
            'storage_path' => 'media/originals/clip.mp4',
            'is_public' => false,
        ]);

        (new GeneratePoster($media->id))->handle();

        $derivative = MediaDerivative::where('media_id', $media->id)
            ->where('type', 'poster')
            ->first();

        $this->assertNotNull($derivative);
        $this->assertTrue(Storage::disk('public')->exists($derivative->storage_path));
    }
}
