<?php

use App\Livewire\PublicTodoList;
use App\Models\TodoList;
use Livewire\Livewire;

test('a public list is accessible by slug', function () {
    $list = TodoList::factory()->public()->create(['title' => 'Lista pública']);

    $this->get("/l/{$list->slug}")
        ->assertOk()
        ->assertSeeText('Lista pública');
});

test('a private list returns 404 on the public route', function () {
    $list = TodoList::factory()->create(['is_public' => false]);

    $this->get("/l/{$list->slug}")->assertNotFound();
});

test('a password-protected list shows the password wall first', function () {
    $list = TodoList::factory()->public()->withPassword('s3cret')->create();

    Livewire::test(PublicTodoList::class, ['slug' => $list->slug])
        ->assertSet('unlocked', false)
        ->assertSee('Esta lista está protegida');
});

test('wrong password keeps the wall up', function () {
    $list = TodoList::factory()->public()->withPassword('s3cret')->create();

    Livewire::test(PublicTodoList::class, ['slug' => $list->slug])
        ->set('passwordAttempt', 'errado')
        ->call('unlock')
        ->assertHasErrors(['passwordAttempt'])
        ->assertSet('unlocked', false);
});

test('correct password unlocks the list and persists in session', function () {
    $list = TodoList::factory()->public()->withPassword('s3cret')->create();

    Livewire::test(PublicTodoList::class, ['slug' => $list->slug])
        ->set('passwordAttempt', 's3cret')
        ->call('unlock')
        ->assertHasNoErrors()
        ->assertSet('unlocked', true);

    expect(session('public_lists_authorized'))->toContain($list->id);
});

test('a non-readonly public list lets visitors add items', function () {
    $list = TodoList::factory()->public()->create();

    Livewire::test(PublicTodoList::class, ['slug' => $list->slug])
        ->set('newItemTitle', 'Item público')
        ->call('addItem')
        ->assertHasNoErrors();

    expect($list->items()->where('title', 'Item público')->exists())->toBeTrue();
});

test('a readonly public list blocks edits from visitors', function () {
    $list = TodoList::factory()->public()->readonly()->create();
    $item = $list->items()->create([
        'title' => 'Item travado',
        'complexity' => 'medium',
        'estimate' => 'hours',
    ]);

    Livewire::test(PublicTodoList::class, ['slug' => $list->slug])
        ->call('toggleItem', $item->id)
        ->assertStatus(403);
});

test('toggling an item flips the completed timestamp', function () {
    $list = TodoList::factory()->public()->create();
    $item = $list->items()->create([
        'title' => 'Pendente',
        'complexity' => 'low',
        'estimate' => 'hours',
    ]);

    Livewire::test(PublicTodoList::class, ['slug' => $list->slug])
        ->call('toggleItem', $item->id);

    expect($item->fresh()->completed_at)->not->toBeNull();
});
