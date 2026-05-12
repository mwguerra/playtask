<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-[18rem_minmax(0,1fr)] gap-6">
        <aside class="playtask-lists-sidebar">
            <section class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4 space-y-3 shadow-sm">
                <header class="flex items-center justify-between gap-2">
                    <h2 class="text-xs uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">
                        Suas listas
                    </h2>
                    {{ $this->createListAction }}
                </header>

                <ul class="space-y-1.5">
                    @forelse ($this->getLists() as $list)
                        <li>
                            <button
                                type="button"
                                wire:click="selectList({{ $list->id }})"
                                @class([
                                    'playtask-list-item',
                                    'playtask-list-item--active' => $selectedListId === $list->id,
                                    'playtask-list-item--idle' => $selectedListId !== $list->id,
                                ])
                            >
                                <div class="flex items-start gap-2">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $list->title }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $list->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    @if ($list->is_public)
                                        <x-filament::icon icon="heroicon-m-globe-alt" class="h-3.5 w-3.5 text-emerald-500 shrink-0 mt-0.5" />
                                    @endif
                                    @if ($list->requires_password)
                                        <x-filament::icon icon="heroicon-m-lock-closed" class="h-3.5 w-3.5 text-amber-500 shrink-0 mt-0.5" />
                                    @endif
                                    @if ($list->is_readonly)
                                        <x-filament::icon icon="heroicon-m-eye" class="h-3.5 w-3.5 text-gray-400 shrink-0 mt-0.5" />
                                    @endif
                                </div>
                            </button>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-gray-300 dark:border-white/10 p-4 text-center">
                            <p class="text-xs text-gray-500">Nenhuma lista ainda.</p>
                        </li>
                    @endforelse
                </ul>
            </section>
        </aside>

        <section>
            @if ($list = $this->getSelectedList())
                <div class="space-y-5">
                    <header class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="min-w-0">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                                {{ $list->title }}
                            </h1>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-500 font-mono">/{{ $list->slug }}</span>
                                @if ($list->is_public)
                                    <span class="playtask-pill playtask-pill--success">
                                        <x-filament::icon icon="heroicon-m-globe-alt" class="h-3 w-3" />
                                        Pública
                                    </span>
                                @endif
                                @if ($list->is_readonly)
                                    <span class="playtask-pill playtask-pill--warning">
                                        <x-filament::icon icon="heroicon-m-eye" class="h-3 w-3" />
                                        Read-only
                                    </span>
                                @endif
                                @if ($list->requires_password)
                                    <span class="playtask-pill playtask-pill--neutral">
                                        <x-filament::icon icon="heroicon-m-lock-closed" class="h-3 w-3" />
                                        Com senha
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            {{ $this->createItemAction }}
                            {{ $this->configureListAction }}
                            {{ $this->deleteListAction }}
                        </div>
                    </header>

                    @php
                        $pending = $list->items->whereNull('completed_at');
                        $done = $list->items->whereNotNull('completed_at');
                        $total = $list->items->count();
                        $progress = $total > 0 ? (int) round(($done->count() / $total) * 100) : 0;
                    @endphp

                    @if ($total > 0)
                        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                <span>{{ $done->count() }}/{{ $total }} concluídos</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $progress }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 transition-all" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>
                    @endif

                    <div class="rounded-xl border border-gray-200 dark:border-white/10 divide-y divide-gray-100 dark:divide-white/5 bg-white dark:bg-gray-900 overflow-hidden">
                        @forelse ($list->items as $item)
                            <div class="playtask-todo-row">
                                <button
                                    type="button"
                                    wire:click="toggleItem({{ $item->id }})"
                                    @class([
                                        'playtask-check',
                                        'playtask-check--done' => $item->isCompleted(),
                                        'playtask-check--idle' => ! $item->isCompleted(),
                                    ])
                                    aria-label="Alternar conclusão"
                                >
                                    @if ($item->isCompleted())
                                        <x-filament::icon icon="heroicon-m-check" class="h-3 w-3" />
                                    @endif
                                </button>

                                <div class="flex-1 min-w-0">
                                    <div @class([
                                        'text-sm font-medium',
                                        'line-through text-gray-400' => $item->isCompleted(),
                                        'text-gray-900 dark:text-white' => ! $item->isCompleted(),
                                    ])>
                                        {{ $item->title }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                        @if ($item->complexity)
                                            <span class="playtask-pill playtask-pill--neutral">
                                                <x-filament::icon :icon="$item->complexity->getIcon()" class="h-3 w-3" />
                                                {{ $item->complexity->getLabel() }}
                                            </span>
                                        @endif
                                        @if ($item->estimate)
                                            <span class="playtask-pill playtask-pill--neutral">
                                                <x-filament::icon :icon="$item->estimate->getIcon()" class="h-3 w-3" />
                                                {{ $item->estimate->getLabel() }}
                                            </span>
                                        @endif
                                        @foreach (($item->tags ?? []) as $tag)
                                            <span class="playtask-pill playtask-pill--tag">#{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="playtask-todo-actions">
                                    {{ ($this->editItemAction)(['item' => $item->id]) }}
                                    {{ ($this->deleteItemAction)(['item' => $item->id]) }}
                                </div>
                            </div>
                        @empty
                            <div class="p-10 text-center">
                                <div class="mx-auto h-12 w-12 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-3">
                                    <x-filament::icon icon="heroicon-o-clipboard-document-list" class="h-6 w-6 text-gray-400" />
                                </div>
                                <p class="text-sm text-gray-500">Nenhum item ainda. Clique em <span class="font-semibold">Novo item</span> para começar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-gray-300 dark:border-white/10 p-12 text-center">
                    <div class="mx-auto h-14 w-14 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center mb-4">
                        <x-filament::icon icon="heroicon-o-list-bullet" class="h-7 w-7 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nenhuma lista selecionada</h3>
                    <p class="mt-1 text-sm text-gray-500">Crie uma lista para começar a organizar suas tarefas.</p>
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
