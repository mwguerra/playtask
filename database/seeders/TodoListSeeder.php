<?php

namespace Database\Seeders;

use App\Enums\Complexity;
use App\Enums\Estimate;
use App\Models\TodoItem;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TodoListSeeder extends Seeder
{
    public function run(): void
    {
        $marcelo = User::where('email', 'marcelo@playtask.test')->first();
        $beta = User::where('email', 'beta@playtask.test')->first();

        if ($marcelo === null || $beta === null) {
            return;
        }

        $public = TodoList::factory()->for($marcelo)->public()->create([
            'title' => 'Roadmap PlayTask',
            'slug' => 'roadmap-playtask',
        ]);

        TodoItem::factory()->for($public)->create([
            'title' => 'Lançar landing page',
            'complexity' => Complexity::Medium,
            'estimate' => Estimate::Days,
            'tags' => ['marketing', 'launch'],
            'completed_at' => now()->subDay(),
            'started_at' => now()->subDays(2),
        ]);

        TodoItem::factory()->for($public)->create([
            'title' => 'Configurar Reverb em produção',
            'complexity' => Complexity::High,
            'estimate' => Estimate::Weeks,
            'tags' => ['devops', 'reverb'],
        ]);

        TodoItem::factory()->for($public)->create([
            'title' => 'Escrever testes Pest do MyLists',
            'complexity' => Complexity::Medium,
            'estimate' => Estimate::Hours,
            'tags' => ['testing'],
            'started_at' => now(),
        ]);

        $protected = TodoList::factory()->for($marcelo)->public()->withPassword('secret')->create([
            'title' => 'Brainstorm secreto',
            'slug' => 'brainstorm-secreto',
        ]);

        TodoItem::factory(3)->for($protected)->create();

        $readonly = TodoList::factory()->for($marcelo)->public()->readonly()->create([
            'title' => 'Changelog público',
            'slug' => 'changelog',
        ]);

        TodoItem::factory()->for($readonly)->completed()->create([
            'title' => 'v0.1.0 — Beta privado',
            'tags' => ['release'],
        ]);

        $private = TodoList::factory()->for($beta)->create([
            'title' => 'Tarefas do Beta User',
            'slug' => 'beta-tasks-'.Str::random(4),
        ]);

        TodoItem::factory(5)->for($private)->create();
    }
}
