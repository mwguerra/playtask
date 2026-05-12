<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TodoItem;
use App\Models\TodoList;
use Filament\Widgets\ChartWidget;

class AdminActivityChart extends ChartWidget
{
    protected ?string $heading = 'Atividade dos últimos 14 dias';

    protected ?string $description = 'Itens criados vs. concluídos por dia';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $listIds = TodoList::where('user_id', auth()->id())->pluck('id');

        $days = collect(range(13, 0))->map(fn (int $d) => now()->subDays($d)->startOfDay());

        $created = $days->map(fn ($day) => TodoItem::whereIn('todo_list_id', $listIds)
            ->whereDate('created_at', $day)
            ->count())->all();

        $completed = $days->map(fn ($day) => TodoItem::whereIn('todo_list_id', $listIds)
            ->whereDate('completed_at', $day)
            ->count())->all();

        return [
            'labels' => $days->map(fn ($d) => $d->format('d/m'))->all(),
            'datasets' => [
                [
                    'label' => 'Criados',
                    'data' => $created,
                    'borderColor' => '#6366f1',
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Concluídos',
                    'data' => $completed,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.15)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
