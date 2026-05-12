<?php

namespace Database\Factories;

use App\Models\BetaSignup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BetaSignup>
 */
class BetaSignupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
