<?php

namespace App\Filament\Superadmin\Resources\BetaSignups;

use App\Filament\Superadmin\Resources\BetaSignups\Pages\ListBetaSignups;
use App\Filament\Superadmin\Resources\BetaSignups\Tables\BetaSignupsTable;
use App\Models\BetaSignup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BetaSignupResource extends Resource
{
    protected static ?string $model = BetaSignup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?string $navigationLabel = 'Inscritos no Beta';

    protected static ?string $modelLabel = 'Inscrito no Beta';

    protected static ?string $pluralModelLabel = 'Inscritos no Beta';

    public static function table(Table $table): Table
    {
        return BetaSignupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBetaSignups::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
