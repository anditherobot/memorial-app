<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Task> */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'completed', 'blocked']),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'category' => $this->faker->randomElement(['bug', 'feature', 'enhancement', 'maintenance', 'documentation']),
            'assigned_to' => $this->faker->boolean(70) ? User::factory() : null,
            'due_date' => $this->faker->boolean(60) ? $this->faker->dateTimeBetween('now', '+30 days') : null,
            'notes' => $this->faker->boolean(40) ? $this->faker->paragraph() : null,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'todo',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function blocked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'blocked',
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
        ]);
    }

    public function high(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
        ]);
    }

    public function bug(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'bug',
            'priority' => $this->faker->randomElement(['high', 'urgent']),
        ]);
    }

    public function feature(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'feature',
        ]);
    }
}