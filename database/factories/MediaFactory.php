<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Media> */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $isImage = $this->faker->boolean(80);
        $mime = $isImage ? $this->faker->randomElement(['image/jpeg','image/png']) : 'video/mp4';
        $ext = match ($mime) {
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'video/mp4' => '.mp4',
            default => ($isImage ? '.jpg' : '.mp4'),
        };
        return [
            'original_filename' => $this->faker->unique()->lexify('file-????') . $ext,
            'mime_type' => $mime,
            'size_bytes' => $this->faker->numberBetween(50_000, 5_000_000),
            'width' => $isImage ? $this->faker->numberBetween(640, 4096) : null,
            'height' => $isImage ? $this->faker->numberBetween(480, 2160) : null,
            'duration_seconds' => $isImage ? null : $this->faker->numberBetween(1, 600),
            'hash' => hash('sha256', $this->faker->unique()->uuid()),
            'storage_path' => 'media/' . $this->faker->uuid() . $ext,
            'is_public' => $this->faker->boolean(),
        ];
    }
}
