<?php

namespace App\Filament\Resources\Sections\Tables;

use App\Filament\Tables\Columns\MaskedTextColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MaskedTextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('color')
                    ->label('Couleur')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('icon')
                    ->label('Icone')
                    ->searchable(),
                MaskedTextColumn::make('store.name')
                    ->label('Magasin')
                    ->searchable(),
                MaskedTextColumn::make('store.user.email')
                    ->label('Utilisateur')
                    ->email()
                    ->searchable(),
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
