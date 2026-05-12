<?php

namespace App\Observers;

use App\Events\TodoItemCreated;
use App\Events\TodoItemDeleted;
use App\Events\TodoItemUpdated;
use App\Models\TodoItem;

class TodoItemObserver
{
    public function created(TodoItem $todoItem): void
    {
        TodoItemCreated::dispatch($todoItem);
    }

    public function updated(TodoItem $todoItem): void
    {
        TodoItemUpdated::dispatch($todoItem);
    }

    public function deleted(TodoItem $todoItem): void
    {
        TodoItemDeleted::dispatch($todoItem);
    }
}
