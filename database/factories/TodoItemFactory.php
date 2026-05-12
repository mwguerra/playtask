<?php

namespace Database\Factories;

use App\Enums\Complexity;
use App\Enums\Estimate;
use App\Models\TodoItem;
use App\Models\TodoList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TodoItem>
 */
class TodoItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'todo_list_id' => TodoList::factory(),
            'title' => fake()->sentence(4),
            'complexity' => fake()->randomElement(Complexity::cases()),
            'estimate' => fake()->randomElement(Estimate::cases()),
            'tags' => fake()->randomElements(['backend', 'frontend', 'urgent', 'bug', 'feature', 'docs'], 2),
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function started(): static
    {
        return $this->state(fn () => ['started_at' => now()]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }
}
