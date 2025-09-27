<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MediaDerivative> */
class MediaDerivativeFactory extends Factory
{
    protected $model = MediaDerivative::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['thumbnail', 'medium', 'poster', 'preview']);

        return [
            'media_id' => Media::factory(),
            'type' => $type,
            'storage_path' => 'derivatives/' . $this->faker->uuid() . '_' . $type . '.jpg',
            'width' => $this->faker->numberBetween(100, 800),
            'height' => $this->faker->numberBetween(100, 600),
            'size_bytes' => $this->faker->numberBetween(5_000, 200_000),
        ];
    }

    public function thumbnail(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'thumbnail',
            'width' => 150,
            'height' => 150,
            'size_bytes' => $this->faker->numberBetween(5_000, 25_000),
        ]);
    }

    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'medium',
            'width' => 800,
            'height' => 600,
            'size_bytes' => $this->faker->numberBetween(50_000, 200_000),
        ]);
    }

    public function poster(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'poster',
            'width' => 1280,
            'height' => 720,
            'size_bytes' => $this->faker->numberBetween(100_000, 300_000),
        ]);
    }
}