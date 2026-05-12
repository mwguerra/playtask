<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum Estimate: string implements HasColor, HasIcon, HasLabel
{
    case Hours = 'hours';
    case Days = 'days';
    case Weeks = 'weeks';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hours => 'Horas',
            self::Days => 'Dias',
            self::Weeks => 'Semanas',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Hours => 'success',
            self::Days => 'warning',
            self::Weeks => 'danger',
        };
    }

    public function getIcon(): string|\BackedEnum|\Illuminate\Contracts\Support\Htmlable|null
    {
        return match ($this) {
            self::Hours => Heroicon::Clock,
            self::Days => Heroicon::Sun,
            self::Weeks => Heroicon::CalendarDays,
        };
    }
}
