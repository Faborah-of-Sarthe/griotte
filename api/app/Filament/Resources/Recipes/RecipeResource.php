<?php

namespace App\Filament\Resources\Recipes;

use App\Filament\Resources\Concerns\IsReadOnlyResource;
use App\Filament\Resources\Recipes\Pages\ListRecipes;
use App\Filament\Resources\Recipes\Tables\RecipesTable;
use App\Models\Recipe;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RecipeResource extends Resource
{
    use IsReadOnlyResource;

    protected static ?string $model = Recipe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $modelLabel = 'recette';

    protected static ?string $pluralModelLabel = 'recettes';

    protected static ?string $navigationLabel = 'Recettes';

    protected static string|UnitEnum|null $navigationGroup = 'Consultation';

    protected static ?string $recordTitleAttribute = 'name';

    public static function table(Table $table): Table
    {
        return RecipesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecipes::route('/'),
        ];
    }
}
