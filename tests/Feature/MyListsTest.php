<?php

use App\Filament\Admin\Pages\MyLists;
use App\Models\TodoItem;
use App\Models\TodoList;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('unauthenticated visitors are redirected from the admin panel', function () {
    auth()->logout();
    $this->get('/admin/my-lists')->assertRedirect('/admin/login');
});

test('a user can reach the my lists page', function () {
    $this->get('/admin/my-lists')->assertOk();
});

test('a user creates a list via the action', function () {
    Livewire::test(MyLists::class)
        ->callAction('createList', data: ['title' => 'Minha primeira lista']);

    expect($this->user->todoLists()->where('title', 'Minha primeira lista')->exists())->toBeTrue();
});

test('a list slug is unique across the application', function () {
    TodoList::factory()->create(['slug' => 'minha-primeira-lista']);

    Livewire::test(MyLists::class)
        ->callAction('createList', data: ['title' => 'Minha primeira lista']);

    expect(
        $this->user->todoLists()->where('slug', 'minha-primeira-lista-1')->exists()
    )->toBeTrue();
});

test('a user only sees their own lists', function () {
    $otherUser = User::factory()->create();
    TodoList::factory()->for($otherUser)->create();
    TodoList::factory()->for($this->user)->create(['title' => 'Sou minha']);

    $component = Livewire::test(MyLists::class);
    $lists = $component->instance()->getLists();

    expect($lists)->toHaveCount(1)
        ->and($lists->first()->title)->toBe('Sou minha');
});

test('a user creates an item via the action', function () {
    $list = TodoList::factory()->for($this->user)->create();

    Livewire::test(MyLists::class)
        ->set('selectedListId', $list->id)
        ->callAction('createItem', data: [
            'title' => 'Comprar pão',
            'complexity' => 'medium',
            'estimate' => 'hours',
            'tags' => ['groceries'],
        ]);

    expect($list->items()->where('title', 'Comprar pão')->exists())->toBeTrue();
});

test('toggling an item flips completed_at', function () {
    $list = TodoList::factory()->for($this->user)->create();
    $item = TodoItem::factory()->for($list)->create();

    Livewire::test(MyLists::class)
        ->set('selectedListId', $list->id)
        ->call('toggleItem', $item->id);

    expect($item->fresh()->completed_at)->not->toBeNull();

    Livewire::test(MyLists::class)
        ->set('selectedListId', $list->id)
        ->call('toggleItem', $item->id);

    expect($item->fresh()->completed_at)->toBeNull();
});

test('a user cannot toggle an item belonging to someone else', function () {
    $otherUser = User::factory()->create();
    $otherList = TodoList::factory()->for($otherUser)->create();
    $foreignItem = TodoItem::factory()->for($otherList)->create();

    Livewire::test(MyLists::class)
        ->call('toggleItem', $foreignItem->id)
        ->assertStatus(403);
});

test('list configuration enforces unique slug ignoring own record', function () {
    $list = TodoList::factory()->for($this->user)->create(['slug' => 'slug-original']);
    TodoList::factory()->create(['slug' => 'slug-de-outro']);

    Livewire::test(MyLists::class)
        ->set('selectedListId', $list->id)
        ->callAction('configureList', data: [
            'list' => $list->id,
            'title' => 'Atualizado',
            'slug' => 'slug-original',
            'is_public' => true,
            'is_readonly' => false,
            'requires_password' => false,
        ])->assertHasNoActionErrors();

    expect($list->fresh()->is_public)->toBeTrue();
});
