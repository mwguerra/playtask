<?php

namespace App\Filament\Superadmin\Widgets;

use App\Models\BetaSignup;
use App\Models\TodoList;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SuperadminStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $users = User::count();
        $active = User::where('is_active', true)->count();
        $superadmins = User::where('is_superadmin', true)->count();

        $signups = BetaSignup::count();
        $signupsLast7 = BetaSignup::where('created_at', '>=', now()->subDays(7))->count();

        $publicLists = TodoList::where('is_public', true)->count();

        return [
            Stat::make('Usuários', $users)
                ->description("{$active} ativos · {$superadmins} superadmins")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Inscritos no Beta', $signups)
                ->description("+{$signupsLast7} nos últimos 7 dias")
                ->descriptionIcon('heroicon-m-envelope')
                ->color($signupsLast7 > 0 ? 'success' : 'gray')
                ->chart(self::signupsTrend()),

            Stat::make('Listas públicas no ar', $publicLists)
                ->description('Total de listas com is_public=true')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('info'),
        ];
    }

    protected static function signupsTrend(): array
    {
        return collect(range(6, 0))
            ->map(fn (int $daysAgo) => BetaSignup::whereDate('created_at', now()->subDays($daysAgo)->toDateString())->count())
            ->all();
    }
}
