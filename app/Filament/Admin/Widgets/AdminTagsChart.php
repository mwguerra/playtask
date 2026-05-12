<?php

namespace App\Filament\Admin\Widgets;

use App\Models\TodoItem;
use App\Models\TodoList;
use Filament\Widgets\ChartWidget;

class AdminTagsChart extends ChartWidget
{
    protected ?string $heading = 'Top 8 tags';

    protected ?string $description = 'Quantidade de itens por tag, considerando todas as suas listas';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $tags = TodoItem::whereIn('todo_list_id', TodoList::where('user_id', auth()->id())->select('id'))
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(8);

        if ($tags->isEmpty()) {
            return [
                'labels' => ['Sem tags ainda'],
                'datasets' => [[
                    'label' => 'Itens',
                    'data' => [1],
                    'backgroundColor' => ['rgba(156, 163, 175, 0.4)'],
                    'borderColor' => ['rgba(156, 163, 175, 1)'],
                ]],
            ];
        }

        $palette = [
            '#6366f1', '#22c55e', '#f59e0b', '#ef4444',
            '#06b6d4', '#a855f7', '#ec4899', '#84cc16',
        ];

        return [
            'labels' => $tags->keys()->map(fn ($t) => "#{$t}")->all(),
            'datasets' => [[
                'label' => 'Itens',
                'data' => $tags->values()->all(),
                'backgroundColor' => array_slice($palette, 0, $tags->count()),
                'borderColor' => array_slice($palette, 0, $tags->count()),
                'borderWidth' => 1,
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'right',
                    'labels' => ['boxWidth' => 12, 'font' => ['size' => 11]],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
