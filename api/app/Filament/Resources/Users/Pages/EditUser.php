<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Actions\ValidateUserAccountAction;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            ValidateUserAccountAction::make($this->getRecord()),
            DeleteAction::make()
                ->authorize(fn (User $record): bool => UserResource::canDelete($record))
                ->visible(fn (User $record): bool => UserResource::canDelete($record)),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! UserResource::canChangeAdminStatus($this->getRecord())) {
            unset($data['is_admin']);
        }

        return $data;
    }
}
