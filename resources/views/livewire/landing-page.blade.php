<div>
    <section class="relative isolate overflow-hidden">
        <div class="mx-auto max-w-5xl px-6 py-24 sm:py-32 text-center">
            <h1 class="text-4xl sm:text-6xl font-bold tracking-tight">
                PlayTask
            </h1>
            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Todo lists colaborativas em tempo real, compartilháveis por link público — com password wall opcional. SaaS por convite.
            </p>

            <div class="mt-12 mx-auto max-w-md">
                @if ($signedUp)
                    <div class="rounded-xl border border-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 dark:border-emerald-500/30 p-6 text-emerald-800 dark:text-emerald-200">
                        Obrigado! Você está na lista do Beta. Avisaremos quando seu convite estiver pronto.
                    </div>
                @else
                    <form wire:submit="subscribe" class="space-y-3">
                        <label for="email" class="block text-sm font-medium text-left text-gray-700 dark:text-gray-300">
                            Quer receber um convite para o Beta?
                        </label>
                        <div class="flex gap-2">
                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                placeholder="voce@exemplo.com"
                                class="flex-1 rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                required
                            >
                            <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                                Inscrever
                            </button>
                        </div>
                        @error('email')
                            <p class="text-sm text-red-600 dark:text-red-400 text-left">{{ $message }}</p>
                        @enderror
                    </form>
                @endif
            </div>
        </div>
    </section>

    <section class="border-t border-gray-200 dark:border-white/5 py-16">
        <div class="mx-auto max-w-5xl px-6 grid sm:grid-cols-3 gap-8 text-center">
            <div>
                <div class="text-3xl">🔗</div>
                <h3 class="mt-3 font-semibold">Compartilháveis</h3>
                <p class="text-sm text-gray-500 mt-1">Cada lista tem um slug único. Compartilhe um link e pronto.</p>
            </div>
            <div>
                <div class="text-3xl">🔒</div>
                <h3 class="mt-3 font-semibold">Protegidas</h3>
                <p class="text-sm text-gray-500 mt-1">Defina senha por lista. Read-only ou colaborativa, você escolhe.</p>
            </div>
            <div>
                <div class="text-3xl">⚡</div>
                <h3 class="mt-3 font-semibold">Tempo real</h3>
                <p class="text-sm text-gray-500 mt-1">Updates instantâneos via WebSocket (Laravel Reverb).</p>
            </div>
        </div>
    </section>
</div>
