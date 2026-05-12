<?php

namespace App\Models;

use App\Enums\Complexity;
use App\Enums\Estimate;
use App\Observers\TodoItemObserver;
use Database\Factories\TodoItemFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([TodoItemObserver::class])]
class TodoItem extends Model
{
    /** @use HasFactory<TodoItemFactory> */
    use HasFactory;

    protected $fillable = [
        'todo_list_id',
        'title',
        'complexity',
        'estimate',
        'tags',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'complexity' => Complexity::class,
            'estimate' => Estimate::class,
            'tags' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isStarted(): bool
    {
        return $this->started_at !== null && $this->completed_at === null;
    }
}
