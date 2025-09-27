<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $published = $this->faker->boolean(90);
        return [
            'title' => $this->faker->sentence(6),
            'body' => collect(range(1, $this->faker->numberBetween(2, 6)))
                ->map(fn () => '<p>'.$this->faker->paragraph().'</p>')
                ->implode("\n"),
            'is_published' => $published,
            'published_at' => $published ? $this->faker->dateTimeBetween('-30 days', 'now') : null,
            'author_name' => $this->faker->name(),
        ];
    }
}

