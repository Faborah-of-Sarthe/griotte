<?php

namespace App\Filament\Resources\Recipes\Tables;

use App\Filament\Tables\Columns\MaskedTextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MaskedTextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                MaskedTextColumn::make('description')
                    ->label('Description')
                    ->toggleable(isToggledHiddenByDefault: true),
                MaskedTextColumn::make('link')
                    ->label('Lien')
                    ->searchable(),
                MaskedTextColumn::make('user.email')
                    ->label('Utilisateur')
                    ->email()
                    ->searchable(),
                IconColumn::make('to_make')
                    ->label('A faire')
                    ->boolean(),
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
                TernaryFilter::make('to_make')
                    ->label('A faire'),
            ]);
    }
}
