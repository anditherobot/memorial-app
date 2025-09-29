<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'original_path' => $this->faker->imageUrl(),
            'display_path' => $this->faker->imageUrl(),
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1000, 5000),
            'width' => $this->faker->numberBetween(600, 4000),
            'height' => $this->faker->numberBetween(600, 4000),
            'variants' => [],
            'status' => 'pending',
            'error_message' => null,
        ];
    }
}
