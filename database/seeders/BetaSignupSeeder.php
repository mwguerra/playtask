<?php

namespace Database\Seeders;

use App\Models\BetaSignup;
use Illuminate\Database\Seeder;

class BetaSignupSeeder extends Seeder
{
    public function run(): void
    {
        BetaSignup::factory(15)->create();

        BetaSignup::factory()->create(['email' => 'fan@playtask.test']);
        BetaSignup::factory()->create(['email' => 'curious@playtask.test']);
    }
}
