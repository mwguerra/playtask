<?php

namespace App\Policies;

use App\Models\TodoItem;
use App\Models\User;

class TodoItemPolicy
{
    public function view(User $user, TodoItem $todoItem): bool
    {
        return $user->id === $todoItem->todoList?->user_id;
    }

    public function create(User $user, TodoItem $todoItem): bool
    {
        return $user->id === $todoItem->todoList?->user_id;
    }

    public function update(User $user, TodoItem $todoItem): bool
    {
        return $user->id === $todoItem->todoList?->user_id;
    }

    public function delete(User $user, TodoItem $todoItem): bool
    {
        return $user->id === $todoItem->todoList?->user_id;
    }
}
