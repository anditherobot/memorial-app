<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemorialEvent>
 */
class MemorialEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = ['funeral', 'viewing', 'burial', 'repass'];
        $eventType = $this->faker->randomElement($eventTypes);

        return [
            'event_type' => $eventType,
            'title' => $this->faker->sentence(),
            'date' => $this->faker->dateTimeBetween('now', '+6 months')->format('Y-m-d'),
            'time' => $this->faker->time('H:i'),
            'venue_name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'contact_phone' => $this->faker->phoneNumber(),
            'contact_email' => $this->faker->email(),
            'notes' => $this->faker->optional()->paragraph(),
            'poster_media_id' => null,
            'is_active' => true,
        ];
    }
}
