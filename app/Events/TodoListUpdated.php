<?php

namespace App\Events;

use App\Models\TodoList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TodoListUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TodoList $todoList) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->todoList->user_id}.lists"),
            $this->todoList->is_public
                ? new Channel("todo-list.{$this->todoList->id}")
                : new PrivateChannel("todo-list.{$this->todoList->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'list_id' => $this->todoList->id,
            'title' => $this->todoList->title,
            'slug' => $this->todoList->slug,
            'is_public' => $this->todoList->is_public,
            'is_readonly' => $this->todoList->is_readonly,
        ];
    }
}
