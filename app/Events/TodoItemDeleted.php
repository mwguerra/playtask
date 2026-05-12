<?php

namespace App\Events;

use App\Models\TodoItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TodoItemDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public TodoItem $item) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $list = $this->item->todoList;

        return [
            $list && $list->is_public
                ? new Channel("todo-list.{$this->item->todo_list_id}")
                : new PrivateChannel("todo-list.{$this->item->todo_list_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'item_id' => $this->item->id,
            'todo_list_id' => $this->item->todo_list_id,
        ];
    }
}
