<?php

namespace App\Filament\Resources\Sections;

use App\Filament\Resources\Concerns\IsReadOnlyResource;
use App\Filament\Resources\Sections\Pages\ListSections;
use App\Filament\Resources\Sections\Tables\SectionsTable;
use App\Models\Section;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SectionResource extends Resource
{
    use IsReadOnlyResource;

    protected static ?string $model = Section::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;

    protected static ?string $modelLabel = 'rayon';

    protected static ?string $pluralModelLabel = 'rayons';

    protected static ?string $navigationLabel = 'Rayons';

    protected static string|UnitEnum|null $navigationGroup = 'Consultation';

    protected static ?string $recordTitleAttribute = 'name';

    public static function table(Table $table): Table
    {
        return SectionsTable::configure($table);
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
            'index' => ListSections::route('/'),
        ];
    }
}
