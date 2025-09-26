<?php

namespace Database\Factories;

use App\Models\Wish;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Wish> */
class WishFactory extends Factory
{
    protected $model = Wish::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'message' => $this->faker->sentences(asText: true),
            'submitted_ip' => $this->faker->ipv4(),
            'is_approved' => $this->faker->boolean(80),
        ];
    }
}

