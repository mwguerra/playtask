<?php

namespace App\Filament\Admin\Pages;

use App\Enums\Complexity;
use App\Enums\Estimate;
use App\Models\TodoItem;
use App\Models\TodoList;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Size;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use UnitEnum;

class MyLists extends Page implements HasActions
{
    use InteractsWithActions;

    protected string $view = 'filament.admin.pages.my-lists';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ListBullet;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?string $title = 'Minhas listas';

    protected static ?string $navigationLabel = 'Minhas listas';

    protected static ?int $navigationSort = 1;

    public ?int $selectedListId = null;

    protected function user(): User
    {
        return auth()->user();
    }

    public function mount(): void
    {
        $this->selectedListId = $this->user()->todoLists()->latest()->value('id');
    }

    public function getListeners(): array
    {
        $userId = $this->user()->id;
        $userBase = 'echo-private:App.Models.User.'.$userId.'.lists';

        $listeners = [
            $userBase.',.App\\Events\\TodoListCreated' => 'onRealtimeChange',
            $userBase.',.App\\Events\\TodoListUpdated' => 'onRealtimeChange',
            $userBase.',.App\\Events\\TodoListDeleted' => 'onRealtimeChange',
        ];

        if ($this->selectedListId !== null) {
            $base = 'echo-private:todo-list.'.$this->selectedListId;
            $listeners[$base.',.App\\Events\\TodoItemCreated'] = 'onRealtimeChange';
            $listeners[$base.',.App\\Events\\TodoItemUpdated'] = 'onRealtimeChange';
            $listeners[$base.',.App\\Events\\TodoItemDeleted'] = 'onRealtimeChange';
        }

        return $listeners;
    }

    public function onRealtimeChange(): void
    {
        // Triggers a Livewire re-render with fresh data from the database.
    }

    public function getLists(): Collection
    {
        return $this->user()->todoLists()->latest()->get();
    }

    public function getSelectedList(): ?TodoList
    {
        if ($this->selectedListId === null) {
            return null;
        }

        return $this->user()->todoLists()->with('items')->find($this->selectedListId);
    }

    public function selectList(int $id): void
    {
        if ($this->user()->todoLists()->whereKey($id)->exists()) {
            $this->selectedListId = $id;
        }
    }

    public function createListAction(): Action
    {
        return Action::make('createList')
            ->label('Nova lista')
            ->tooltip('Nova lista')
            ->iconButton()
            ->icon(Heroicon::Plus)
            ->color('primary')
            ->size(Size::Small)
            ->modalHeading('Nova lista')
            ->slideOver()
            ->schema([
                Section::make('Lista')
                    ->schema([
                        TextInput::make('title')->label('Título')->required()->maxLength(255),
                    ]),
            ])
            ->action(function (array $data): void {
                $list = $this->user()->todoLists()->create([
                    'title' => $data['title'],
                    'slug' => $this->uniqueSlug($data['title']),
                ]);

                $this->selectedListId = $list->id;

                Notification::make()->success()->title('Lista criada')->send();
            });
    }

    public function deleteListAction(): Action
    {
        return Action::make('deleteList')
            ->label('Excluir lista')
            ->tooltip('Excluir lista')
            ->iconButton()
            ->icon(Heroicon::Trash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Excluir lista?')
            ->modalDescription('Esta ação remove a lista e todos os seus itens. Não há como desfazer.')
            ->action(function (): void {
                $list = $this->getSelectedList();

                if ($list === null) {
                    return;
                }

                $list->delete();

                $this->selectedListId = $this->user()->todoLists()->latest()->value('id');

                Notification::make()->success()->title('Lista excluída')->send();
            });
    }

    public function configureListAction(): Action
    {
        return Action::make('configureList')
            ->label('Configurar lista')
            ->tooltip('Configurar lista')
            ->iconButton()
            ->icon(Heroicon::Cog6Tooth)
            ->color('gray')
            ->modalHeading('Configurações da lista')
            ->slideOver()
            ->fillForm(function (): array {
                $list = $this->getSelectedList();

                if ($list === null) {
                    return [];
                }

                return [
                    'title' => $list->title,
                    'slug' => $list->slug,
                    'is_public' => $list->is_public,
                    'is_readonly' => $list->is_readonly,
                    'requires_password' => $list->requires_password,
                ];
            })
            ->schema([
                Section::make('Identificação')
                    ->schema([
                        TextInput::make('title')->label('Título')->required()->maxLength(255),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->rule(fn () => Rule::unique('todo_lists', 'slug')->ignore($this->selectedListId)),
                    ])->columns(2),
                Fieldset::make('Visibilidade')
                    ->schema([
                        Toggle::make('is_public')->label('Pública'),
                        Toggle::make('is_readonly')->label('Somente leitura'),
                    ])->columns(2),
                Fieldset::make('Proteção por senha')
                    ->schema([
                        Toggle::make('requires_password')->label('Exige senha')->live(),
                        TextInput::make('password')
                            ->label('Senha')
                            ->password()
                            ->revealable()
                            ->visible(fn ($get) => (bool) $get('requires_password'))
                            ->helperText('Deixe em branco para manter a senha atual.'),
                    ])->columns(2),
            ])
            ->action(function (array $data): void {
                $list = $this->getSelectedList();

                if ($list === null) {
                    return;
                }

                $update = [
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'is_public' => $data['is_public'] ?? false,
                    'is_readonly' => $data['is_readonly'] ?? false,
                    'requires_password' => $data['requires_password'] ?? false,
                ];

                if ($update['requires_password']) {
                    if (! empty($data['password'])) {
                        $update['password'] = $data['password'];
                    }
                } else {
                    $update['password'] = null;
                }

                $list->update($update);

                Notification::make()->success()->title('Configurações salvas')->send();
            });
    }

    public function createItemAction(): Action
    {
        return Action::make('createItem')
            ->label('Novo item')
            ->tooltip('Novo item')
            ->iconButton()
            ->icon(Heroicon::Plus)
            ->color('primary')
            ->size(Size::Small)
            ->slideOver()
            ->modalHeading('Adicionar item')
            ->visible(fn () => $this->getSelectedList() !== null)
            ->schema($this->itemSchema())
            ->action(function (array $data): void {
                $list = $this->getSelectedList();

                if ($list === null) {
                    return;
                }

                $list->items()->create([
                    'title' => $data['title'],
                    'complexity' => $data['complexity'],
                    'estimate' => $data['estimate'],
                    'tags' => $data['tags'] ?? [],
                ]);

                Notification::make()->success()->title('Item criado')->send();
            });
    }

    public function editItemAction(): Action
    {
        return Action::make('editItem')
            ->label('Editar item')
            ->tooltip('Editar item')
            ->iconButton()
            ->size(Size::Small)
            ->color('gray')
            ->icon(Heroicon::PencilSquare)
            ->modalHeading('Editar item')
            ->slideOver()
            ->fillForm(function (array $arguments): array {
                $item = TodoItem::findOrFail($arguments['item']);
                abort_unless($item->todoList?->user_id === $this->user()->id, 403);

                return [
                    'title' => $item->title,
                    'complexity' => $item->complexity?->value,
                    'estimate' => $item->estimate?->value,
                    'tags' => $item->tags ?? [],
                ];
            })
            ->schema(array_merge([Hidden::make('item')], $this->itemSchema()))
            ->action(function (array $data, array $arguments): void {
                $item = TodoItem::findOrFail($arguments['item']);
                abort_unless($item->todoList?->user_id === $this->user()->id, 403);

                $item->update([
                    'title' => $data['title'],
                    'complexity' => $data['complexity'],
                    'estimate' => $data['estimate'],
                    'tags' => $data['tags'] ?? [],
                ]);

                Notification::make()->success()->title('Item atualizado')->send();
            });
    }

    public function deleteItemAction(): Action
    {
        return Action::make('deleteItem')
            ->label('Excluir item')
            ->tooltip('Excluir item')
            ->iconButton()
            ->size(Size::Small)
            ->icon(Heroicon::Trash)
            ->color('danger')
            ->modalHeading('Excluir este item?')
            ->requiresConfirmation()
            ->action(function (array $arguments): void {
                $item = TodoItem::findOrFail($arguments['item']);
                abort_unless($item->todoList?->user_id === $this->user()->id, 403);

                $item->delete();

                Notification::make()->success()->title('Item excluído')->send();
            });
    }

    public function toggleItem(int $id): void
    {
        $item = TodoItem::findOrFail($id);
        abort_unless($item->todoList?->user_id === $this->user()->id, 403);

        $item->update([
            'started_at' => $item->started_at ?? now(),
            'completed_at' => $item->completed_at ? null : now(),
        ]);
    }

    protected function itemSchema(): array
    {
        return [
            Section::make('Item')
                ->schema([
                    TextInput::make('title')->label('Título')->required()->maxLength(255),
                ]),
            Fieldset::make('Classificação')
                ->schema([
                    Select::make('complexity')
                        ->label('Complexidade')
                        ->options(collect(Complexity::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()]))
                        ->default(Complexity::Medium->value)
                        ->required(),
                    Select::make('estimate')
                        ->label('Estimativa')
                        ->options(collect(Estimate::cases())->mapWithKeys(fn ($e) => [$e->value => $e->getLabel()]))
                        ->default(Estimate::Hours->value)
                        ->required(),
                ])->columns(2),
            Section::make('Tags')
                ->schema([
                    TagsInput::make('tags')
                        ->label('Tags')
                        ->suggestions($this->userTagSuggestions())
                        ->reorderable(false),
                ]),
        ];
    }

    protected function userTagSuggestions(): array
    {
        return TodoItem::whereIn('todo_list_id', $this->user()->todoLists()->select('id'))
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    protected function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'lista';
        $candidate = $base;
        $i = 1;

        while (TodoList::where('slug', $candidate)->exists()) {
            $candidate = "{$base}-{$i}";
            $i++;
        }

        return $candidate;
    }
}
