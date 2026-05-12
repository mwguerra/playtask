<?php

namespace App\Models;

use App\Observers\TodoListObserver;
use Database\Factories\TodoListFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TodoListObserver::class])]
class TodoList extends Model
{
    /** @use HasFactory<TodoListFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'is_public',
        'is_readonly',
        'requires_password',
        'password',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_readonly' => 'boolean',
            'requires_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TodoItem::class)->orderBy('created_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
