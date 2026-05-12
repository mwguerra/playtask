<?php

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}.lists', function (User $user, int $id) {
    return $user->id === $id;
});

Broadcast::channel('todo-list.{listId}', function (User $user, int $listId) {
    $list = TodoList::find($listId);

    if ($list === null) {
        return false;
    }

    return $user->id === $list->user_id;
});
