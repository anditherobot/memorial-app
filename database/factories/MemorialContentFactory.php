<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MemorialContent>
 */
class MemorialContentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contentTypes = ['bio', 'memorial_name', 'memorial_dates', 'contact_info'];
        $contentType = $this->faker->randomElement($contentTypes);

        $contentMap = [
            'bio' => [
                'title' => 'Life Story',
                'content' => $this->faker->paragraphs(3, true),
            ],
            'memorial_name' => [
                'title' => 'Full Name',
                'content' => $this->faker->name(),
            ],
            'memorial_dates' => [
                'title' => 'Important Dates',
                'content' => $this->faker->dateTimeBetween('-80 years', '-1 year')->format('M j, Y') . ' - ' . $this->faker->dateTimeBetween('-1 year', 'now')->format('M j, Y'),
            ],
            'contact_info' => [
                'title' => 'Family Contact',
                'content' => "Phone: " . $this->faker->phoneNumber() . "\nEmail: " . $this->faker->email(),
            ],
        ];

        return [
            'content_type' => $contentType,
            'title' => $contentMap[$contentType]['title'],
            'content' => $contentMap[$contentType]['content'],
        ];
    }
}
