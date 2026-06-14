<?php

namespace App\Filament\Resources\Stores\Tables;

use App\Filament\Tables\Columns\MaskedTextColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MaskedTextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                MaskedTextColumn::make('user.email')
                    ->label('Utilisateur')
                    ->email()
                    ->searchable(),
                TextColumn::make('sections_count')
                    ->label('Rayons')
                    ->counts('sections')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Cree le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modifie le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ]);
    }
}
