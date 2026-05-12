<?php

use App\Events\TodoItemCreated;
use App\Events\TodoItemDeleted;
use App\Events\TodoItemUpdated;
use App\Events\TodoListCreated;
use App\Events\TodoListDeleted;
use App\Events\TodoListUpdated;
use App\Models\TodoItem;
use App\Models\TodoList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Facades\Event;

test('creating a list dispatches TodoListCreated', function () {
    Event::fake([TodoListCreated::class]);

    $list = TodoList::factory()->create();

    Event::assertDispatched(TodoListCreated::class, fn ($event) => $event->todoList->is($list));
});

test('updating a list dispatches TodoListUpdated', function () {
    $list = TodoList::factory()->create();

    Event::fake([TodoListUpdated::class]);

    $list->update(['title' => 'Atualizado']);

    Event::assertDispatched(TodoListUpdated::class);
});

test('deleting a list dispatches TodoListDeleted', function () {
    $list = TodoList::factory()->create();

    Event::fake([TodoListDeleted::class]);

    $list->delete();

    Event::assertDispatched(TodoListDeleted::class);
});

test('creating an item dispatches TodoItemCreated', function () {
    Event::fake([TodoItemCreated::class]);

    $item = TodoItem::factory()->create();

    Event::assertDispatched(TodoItemCreated::class, fn ($event) => $event->item->is($item));
});

test('updating an item dispatches TodoItemUpdated', function () {
    $item = TodoItem::factory()->create();

    Event::fake([TodoItemUpdated::class]);

    $item->update(['title' => 'Novo título']);

    Event::assertDispatched(TodoItemUpdated::class);
});

test('deleting an item dispatches TodoItemDeleted', function () {
    $item = TodoItem::factory()->create();

    Event::fake([TodoItemDeleted::class]);

    $item->delete();

    Event::assertDispatched(TodoItemDeleted::class);
});

test('TodoListCreated broadcasts on the owner private channel', function () {
    $list = TodoList::factory()->create();
    $event = new TodoListCreated($list);

    $channels = $event->broadcastOn();

    expect($channels)->toHaveCount(1)
        ->and($channels[0])->toBeInstanceOf(PrivateChannel::class)
        ->and($channels[0]->name)->toBe("private-App.Models.User.{$list->user_id}.lists");
});

test('TodoListUpdated broadcasts publicly when list is public', function () {
    $list = TodoList::factory()->public()->create();
    $event = new TodoListUpdated($list);

    $channels = $event->broadcastOn();
    $listChannel = collect($channels)->first(fn ($c) => str_contains($c->name, "todo-list.{$list->id}"));

    expect($listChannel)->toBeInstanceOf(Channel::class)
        ->and($listChannel)->not->toBeInstanceOf(PrivateChannel::class);
});

test('TodoListUpdated broadcasts privately when list is private', function () {
    $list = TodoList::factory()->create(['is_public' => false]);
    $event = new TodoListUpdated($list);

    $listChannel = collect($event->broadcastOn())
        ->first(fn ($c) => str_contains($c->name, "todo-list.{$list->id}"));

    expect($listChannel)->toBeInstanceOf(PrivateChannel::class);
});

test('TodoItemCreated broadcastWith payload includes core fields', function () {
    $item = TodoItem::factory()->create();
    $event = new TodoItemCreated($item);

    expect($event->broadcastWith())
        ->toHaveKeys(['item_id', 'todo_list_id', 'title', 'complexity', 'estimate', 'tags', 'started_at', 'completed_at']);
});
