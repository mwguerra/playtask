<?php

namespace App\Livewire;

use App\Enums\Complexity;
use App\Enums\Estimate;
use App\Models\TodoList;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class PublicTodoList extends Component
{
    public TodoList $list;

    public bool $unlocked = false;

    #[Validate('required|string|min:1')]
    public string $passwordAttempt = '';

    public string $newItemTitle = '';

    public string $newItemComplexity = 'medium';

    public string $newItemEstimate = 'hours';

    public function mount(string $slug): void
    {
        $list = TodoList::with('items')->where('slug', $slug)->where('is_public', true)->first();

        abort_if($list === null, 404);

        $this->list = $list;

        $authorized = session('public_lists_authorized', []);

        $this->unlocked = ! $list->requires_password || in_array($list->id, $authorized, true);
    }

    public function getListeners(): array
    {
        $base = 'echo:todo-list.'.$this->list->id;

        return [
            $base.',.App\\Events\\TodoItemCreated' => 'refreshList',
            $base.',.App\\Events\\TodoItemUpdated' => 'refreshList',
            $base.',.App\\Events\\TodoItemDeleted' => 'refreshList',
            $base.',.App\\Events\\TodoListUpdated' => 'refreshList',
            $base.',.App\\Events\\TodoListDeleted' => 'redirectToHome',
        ];
    }

    public function refreshList(): void
    {
        $this->list->refresh()->load('items');
    }

    public function redirectToHome(): void
    {
        $this->redirect(route('home'));
    }

    public function unlock(): void
    {
        $this->validate(['passwordAttempt' => 'required|string|min:1']);

        if (! $this->list->requires_password || ! Hash::check($this->passwordAttempt, (string) $this->list->password)) {
            $this->addError('passwordAttempt', 'Senha incorreta.');

            return;
        }

        $authorized = session('public_lists_authorized', []);
        $authorized[] = $this->list->id;
        session(['public_lists_authorized' => array_values(array_unique($authorized))]);

        $this->unlocked = true;
        $this->passwordAttempt = '';
    }

    public function addItem(): void
    {
        $this->ensureEditable();
        $this->validate(['newItemTitle' => 'required|string|max:255']);

        $this->list->items()->create([
            'title' => $this->newItemTitle,
            'complexity' => $this->newItemComplexity,
            'estimate' => $this->newItemEstimate,
            'tags' => [],
        ]);

        $this->newItemTitle = '';
        $this->list->refresh()->load('items');
    }

    public function toggleItem(int $itemId): void
    {
        $this->ensureEditable();

        $item = $this->list->items()->findOrFail($itemId);
        $item->update([
            'started_at' => $item->started_at ?? now(),
            'completed_at' => $item->completed_at ? null : now(),
        ]);

        $this->list->refresh()->load('items');
    }

    public function deleteItem(int $itemId): void
    {
        $this->ensureEditable();

        $item = $this->list->items()->findOrFail($itemId);
        $item->delete();

        $this->list->refresh()->load('items');
    }

    protected function ensureEditable(): void
    {
        abort_if(! $this->unlocked || $this->list->is_readonly, 403);
    }

    public function getComplexityCases(): array
    {
        return Complexity::cases();
    }

    public function getEstimateCases(): array
    {
        return Estimate::cases();
    }

    public function render(): View
    {
        return view('livewire.public-todo-list');
    }
}
