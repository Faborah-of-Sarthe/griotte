<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Tables\Columns\MaskedTextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MaskedTextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                MaskedTextColumn::make('comment')
                    ->label('Commentaire')
                    ->toggleable(isToggledHiddenByDefault: true),
                MaskedTextColumn::make('user.email')
                    ->label('Utilisateur')
                    ->email()
                    ->searchable(),
                IconColumn::make('to_buy')
                    ->label('A acheter')
                    ->boolean(),
                IconColumn::make('is_temporary')
                    ->label('Temporaire')
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
                TernaryFilter::make('to_buy')
                    ->label('A acheter'),
                TernaryFilter::make('is_temporary')
                    ->label('Temporaire'),
            ]);
    }
}
