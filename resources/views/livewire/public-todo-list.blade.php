<div class="mx-auto max-w-3xl px-6 py-12">
    <header class="mb-8 flex items-start justify-between gap-3">
        <div>
            <a href="{{ url('/') }}" class="text-xs text-gray-500 hover:underline">← PlayTask</a>
            <h1 class="text-3xl font-bold mt-2">{{ $list->title }}</h1>
            <p class="text-xs text-gray-500 font-mono mt-1">/l/{{ $list->slug }}</p>
        </div>
        @if ($list->is_readonly)
            <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300 px-3 py-1 text-xs font-semibold">Somente leitura</span>
        @endif
    </header>

    @if (! $unlocked)
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-8 max-w-sm mx-auto">
            <h2 class="text-lg font-semibold mb-1">Esta lista está protegida</h2>
            <p class="text-sm text-gray-500 mb-6">Informe a senha para acessar.</p>
            <form wire:submit="unlock" class="space-y-3">
                <input
                    type="password"
                    wire:model="passwordAttempt"
                    placeholder="Senha"
                    autofocus
                    class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-950 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"
                >
                @error('passwordAttempt')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                    Entrar
                </button>
            </form>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-white/5">
            @forelse ($list->items as $item)
                <div class="flex items-start gap-3 p-4">
                    <button
                        type="button"
                        @if (! $list->is_readonly) wire:click="toggleItem({{ $item->id }})" @endif
                        @class([
                            'mt-0.5 h-5 w-5 rounded border flex items-center justify-center shrink-0 transition',
                            'bg-emerald-500 border-emerald-500 text-white' => $item->isCompleted(),
                            'border-gray-300 dark:border-white/20' => ! $item->isCompleted(),
                            'cursor-not-allowed opacity-60' => $list->is_readonly,
                            'hover:border-indigo-500 cursor-pointer' => ! $list->is_readonly && ! $item->isCompleted(),
                        ])
                    >
                        @if ($item->isCompleted())
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor"><path d="M16.7 5.3a1 1 0 010 1.4l-7.5 7.5a1 1 0 01-1.4 0L3.3 9.7a1 1 0 011.4-1.4l3.8 3.8 6.8-6.8a1 1 0 011.4 0z"/></svg>
                        @endif
                    </button>

                    <div class="flex-1 min-w-0">
                        <div @class([
                            'text-sm font-medium',
                            'line-through text-gray-400' => $item->isCompleted(),
                        ])>{{ $item->title }}</div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-2 py-0.5">{{ $item->complexity?->getLabel() }}</span>
                            <span class="rounded-full bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 px-2 py-0.5">{{ $item->estimate?->getLabel() }}</span>
                            @foreach (($item->tags ?? []) as $tag)
                                <span class="rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 px-2 py-0.5">#{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>

                    @if (! $list->is_readonly)
                        <button wire:click="deleteItem({{ $item->id }})" class="text-xs text-red-600 hover:text-red-500" wire:confirm="Excluir este item?">
                            Excluir
                        </button>
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-sm text-gray-500">Nenhum item nesta lista ainda.</div>
            @endforelse

            @if (! $list->is_readonly)
                <form wire:submit="addItem" class="p-4 flex flex-col sm:flex-row gap-2">
                    <input
                        type="text"
                        wire:model="newItemTitle"
                        placeholder="Adicionar item..."
                        class="flex-1 rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-950 px-3 py-2 text-sm"
                    >
                    <select wire:model="newItemComplexity" class="rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-950 px-3 py-2 text-sm">
                        @foreach ($this->getComplexityCases() as $c)
                            @selected($newItemComplexity === $c->value)
                            <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                        @endforeach
                    </select>
                    <select wire:model="newItemEstimate" class="rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-950 px-3 py-2 text-sm">
                        @foreach ($this->getEstimateCases() as $e)
                            @selected($newItemEstimate === $e->value)
                            <option value="{{ $e->value }}">{{ $e->getLabel() }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        Adicionar
                    </button>
                </form>
                @error('newItemTitle')
                    <p class="px-4 pb-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
            @endif
        </div>
    @endif
</div>
