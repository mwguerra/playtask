<?php

namespace App\Filament\Superadmin\Resources\BetaSignups\Pages;

use App\Filament\Superadmin\Resources\BetaSignups\BetaSignupResource;
use Filament\Resources\Pages\ListRecords;

class ListBetaSignups extends ListRecords
{
    protected static string $resource = BetaSignupResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
