<?php

namespace App\Events;

use App\Models\TodoList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TodoListCreated implements ShouldBroadcastNow
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
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'list_id' => $this->todoList->id,
            'title' => $this->todoList->title,
            'slug' => $this->todoList->slug,
            'created_at' => $this->todoList->created_at?->toIso8601String(),
        ];
    }
}
