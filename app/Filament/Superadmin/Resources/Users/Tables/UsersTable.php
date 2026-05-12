<?php

namespace App\Filament\Superadmin\Resources\Users\Tables;

use App\Models\User;
use App\Support\PasswordGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('E-mail')->searchable()->copyable(),
                IconColumn::make('is_active')->label('Ativo')->boolean(),
                IconColumn::make('is_superadmin')->label('Superadmin')->boolean(),
                TextColumn::make('created_at')->label('Criado em')->dateTime('d/m/Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Ativos'),
                TernaryFilter::make('is_superadmin')->label('Superadmins'),
            ])
            ->headerActions([
                self::createUserAction(),
            ])
            ->recordActions([
                self::regeneratePasswordAction(),
                self::toggleActiveAction(),
            ]);
    }

    public static function createUserAction(): Action
    {
        return Action::make('createUser')
            ->label('Novo usuário')
            ->icon('heroicon-o-user-plus')
            ->schema([
                Section::make('Dados')
                    ->schema([
                        TextInput::make('name')->label('Nome')->required()->maxLength(255),
                        TextInput::make('email')->label('E-mail')->email()->required()
                            ->rule(Rule::unique('users', 'email')),
                    ])->columns(2),
                Fieldset::make('Permissões')
                    ->schema([
                        Toggle::make('is_superadmin')->label('Superadmin')->default(false),
                    ]),
            ])
            ->action(function (array $data): void {
                $password = PasswordGenerator::generate();

                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $password,
                    'is_active' => true,
                    'is_superadmin' => (bool) ($data['is_superadmin'] ?? false),
                    'email_verified_at' => now(),
                ]);

                Notification::make()
                    ->title('Usuário criado')
                    ->body("Senha temporária de {$user->email}: {$password} (copie agora — não será exibida novamente)")
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    public static function regeneratePasswordAction(): Action
    {
        return Action::make('regeneratePassword')
            ->label('Regerar senha')
            ->tooltip('Regerar senha')
            ->iconButton()
            ->size(Size::Small)
            ->color('warning')
            ->icon('heroicon-o-key')
            ->requiresConfirmation()
            ->modalHeading('Regerar senha?')
            ->modalDescription('Uma nova senha aleatória será gerada e exibida uma única vez.')
            ->action(function (User $record): void {
                $password = PasswordGenerator::generate();
                $record->forceFill(['password' => bcrypt($password)])->save();

                Notification::make()
                    ->title('Senha regerada')
                    ->body("Nova senha de {$record->email}: {$password}")
                    ->success()
                    ->persistent()
                    ->send();
            });
    }

    public static function toggleActiveAction(): Action
    {
        return Action::make('toggleActive')
            ->label(fn (User $record) => $record->is_active ? 'Desativar' : 'Ativar')
            ->tooltip(fn (User $record) => $record->is_active ? 'Desativar usuário' : 'Ativar usuário')
            ->iconButton()
            ->size(Size::Small)
            ->icon(fn (User $record) => $record->is_active ? 'heroicon-o-no-symbol' : 'heroicon-o-check')
            ->color(fn (User $record) => $record->is_active ? 'danger' : 'success')
            ->requiresConfirmation()
            ->action(function (User $record): void {
                $record->update(['is_active' => ! $record->is_active]);

                Notification::make()
                    ->title($record->is_active ? 'Usuário ativado' : 'Usuário desativado')
                    ->success()
                    ->send();
            });
    }
}
