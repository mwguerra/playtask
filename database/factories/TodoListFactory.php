<?php

namespace Database\Factories;

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TodoList>
 */
class TodoListFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'is_public' => false,
            'is_readonly' => false,
            'requires_password' => false,
            'password' => null,
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => ['is_public' => true]);
    }

    public function readonly(): static
    {
        return $this->state(fn () => ['is_readonly' => true]);
    }

    public function withPassword(string $password = 'secret'): static
    {
        return $this->state(fn () => [
            'requires_password' => true,
            'password' => $password,
        ]);
    }
}
