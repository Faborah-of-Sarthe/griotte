<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Infolists\Components\MaskedTextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                MaskedTextEntry::make('email')
                    ->label('Email')
                    ->email(),
                IconEntry::make('is_admin')
                    ->label('Administrateur')
                    ->boolean(),
                IconEntry::make('email_verified_at')
                    ->label('Compte valide')
                    ->boolean()
                    ->state(fn ($record): bool => filled($record->email_verified_at)),
                TextEntry::make('email_verified_at')
                    ->label('Email verifie le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('two_factor_confirmed_at')
                    ->label('2FA confirmee le')
                    ->dateTime()
                    ->placeholder('-'),
                MaskedTextEntry::make('currentStore.name')
                    ->label('Magasin courant'),
                TextEntry::make('created_at')
                    ->label('Cree le')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label('Modifie le')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('finished_tutorial')
                    ->label('Tutoriel termine')
                    ->boolean(),
            ])
            ->columns(2);
    }
}
