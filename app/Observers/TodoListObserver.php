<?php

namespace App\Observers;

use App\Events\TodoListCreated;
use App\Events\TodoListDeleted;
use App\Events\TodoListUpdated;
use App\Models\TodoList;

class TodoListObserver
{
    public function created(TodoList $todoList): void
    {
        TodoListCreated::dispatch($todoList);
    }

    public function updated(TodoList $todoList): void
    {
        TodoListUpdated::dispatch($todoList);
    }

    public function deleted(TodoList $todoList): void
    {
        TodoListDeleted::dispatch($todoList);
    }
}
