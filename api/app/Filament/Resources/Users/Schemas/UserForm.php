<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Toggle::make('is_admin')
                    ->label('Administrateur')
                    ->disabled(fn (?User $record): bool => $record instanceof User && ! UserResource::canChangeAdminStatus($record))
                    ->dehydrated(fn (?User $record): bool => ! ($record instanceof User) || UserResource::canChangeAdminStatus($record))
                    ->helperText('Autorise l\'acces au back-office Filament. Le compte courant et le dernier admin sont proteges.'),
                DateTimePicker::make('email_verified_at')
                    ->label('Email verifie le')
                    ->seconds(false),
                TextInput::make('password')
                    ->label('Nouveau mot de passe')
                    ->password()
                    ->revealable()
                    ->autocomplete('new-password')
                    ->minLength(8)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText('Laissez vide pour conserver le mot de passe actuel.'),
            ])
            ->columns(2);
    }
}
