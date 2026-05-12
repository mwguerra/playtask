<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TodoItem;
use App\Models\TodoList;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = auth()->id();

        $lists = TodoList::where('user_id', $userId);
        $totalLists = (clone $lists)->count();
        $publicLists = (clone $lists)->where('is_public', true)->count();
        $protectedLists = (clone $lists)->where('requires_password', true)->count();

        $items = TodoItem::whereIn('todo_list_id', (clone $lists)->select('id'));
        $totalItems = (clone $items)->count();
        $completedItems = (clone $items)->whereNotNull('completed_at')->count();
        $pendingItems = $totalItems - $completedItems;
        $progress = $totalItems > 0 ? (int) round(($completedItems / $totalItems) * 100) : 0;

        return [
            Stat::make('Minhas listas', $totalLists)
                ->description("{$publicLists} públicas · {$protectedLists} com senha")
                ->descriptionIcon('heroicon-m-list-bullet')
                ->color('primary'),

            Stat::make('Itens pendentes', $pendingItems)
                ->description("{$completedItems} concluídos · {$totalItems} no total")
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingItems > 0 ? 'warning' : 'success'),

            Stat::make('Progresso geral', "{$progress}%")
                ->description($progress === 100 ? 'Tudo concluído' : 'Em andamento')
                ->descriptionIcon($progress === 100 ? 'heroicon-m-check-circle' : 'heroicon-m-arrow-trending-up')
                ->color($progress === 100 ? 'success' : 'info')
                ->chart(self::progressTrend($userId)),
        ];
    }

    protected static function progressTrend(int $userId): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => TodoItem::whereIn(
                'todo_list_id',
                TodoList::where('user_id', $userId)->select('id')
            )
                ->whereDate('completed_at', now()->subDays($daysAgo)->toDateString())
                ->count())
            ->all();
    }
}
