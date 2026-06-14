<?php

namespace App\Filament\Resources\Stores;

use App\Filament\Resources\Concerns\IsReadOnlyResource;
use App\Filament\Resources\Stores\Pages\ListStores;
use App\Filament\Resources\Stores\Tables\StoresTable;
use App\Models\Store;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StoreResource extends Resource
{
    use IsReadOnlyResource;

    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $modelLabel = 'magasin';

    protected static ?string $pluralModelLabel = 'magasins';

    protected static ?string $navigationLabel = 'Magasins';

    protected static string|UnitEnum|null $navigationGroup = 'Consultation';

    protected static ?string $recordTitleAttribute = 'name';

    public static function table(Table $table): Table
    {
        return StoresTable::configure($table);
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
            'index' => ListStores::route('/'),
        ];
    }
}
