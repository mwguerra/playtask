<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->superadmin()->create([
            'name' => 'Super Admin',
            'email' => 'super@playtask.test',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Marcelo Guerra',
            'email' => 'marcelo@playtask.test',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Beta User',
            'email' => 'beta@playtask.test',
            'password' => 'password',
        ]);
    }
}
