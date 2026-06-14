<?php

namespace App\Filament\Resources\Tags;

use App\Filament\Resources\Concerns\IsReadOnlyResource;
use App\Filament\Resources\Tags\Pages\ListTags;
use App\Filament\Resources\Tags\Tables\TagsTable;
use App\Models\Tag;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TagResource extends Resource
{
    use IsReadOnlyResource;

    protected static ?string $model = Tag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $modelLabel = 'tag';

    protected static ?string $pluralModelLabel = 'tags';

    protected static ?string $navigationLabel = 'Tags';

    protected static string|UnitEnum|null $navigationGroup = 'Consultation';

    protected static ?string $recordTitleAttribute = 'name';

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
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
            'index' => ListTags::route('/'),
        ];
    }
}
