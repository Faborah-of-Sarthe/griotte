<?php

namespace App\Filament\Resources\Users\Tables;

use App\Filament\Actions\ValidateUserAccountAction;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Tables\Columns\MaskedTextColumn;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                MaskedTextColumn::make('email')
                    ->label('Email')
                    ->email()
                    ->searchable(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label('Valide')
                    ->boolean()
                    ->state(fn (User $record): bool => filled($record->email_verified_at))
                    ->sortable(),
                TextColumn::make('email_verified_at')
                    ->label('Email verifie le')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('stores_count')
                    ->label('Magasins')
                    ->counts('stores')
                    ->sortable(),
                TextColumn::make('products_count')
                    ->label('Produits')
                    ->counts('products')
                    ->sortable(),
                TextColumn::make('recipes_count')
                    ->label('Recettes')
                    ->counts('recipes')
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
                TernaryFilter::make('is_admin')
                    ->label('Administrateur'),
                TernaryFilter::make('email_verified_at')
                    ->label('Compte valide')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('email_verified_at'),
                        false: fn ($query) => $query->whereNull('email_verified_at'),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                ValidateUserAccountAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->authorize(fn (User $record): bool => UserResource::canDelete($record))
                    ->visible(fn (User $record): bool => UserResource::canDelete($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords(fn (User $record): bool => UserResource::canDelete($record)),
                ]),
            ]);
    }
}
