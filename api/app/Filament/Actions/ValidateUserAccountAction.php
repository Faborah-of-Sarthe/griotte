<?php

namespace App\Filament\Actions;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ValidateUserAccountAction
{
    public static function make(?User $page_record = null): Action
    {
        return Action::make('validate_user_account')
            ->label('Valider le compte')
            ->requiresConfirmation()
            ->modalHeading('Valider ce compte ?')
            ->modalDescription('Cette action marque l\'adresse email de l\'utilisateur comme verifiee.')
            ->visible(fn (?User $record = null): bool => blank(($page_record ?? $record)?->email_verified_at))
            ->action(function (?User $record = null) use ($page_record): void {
                $user = $page_record ?? $record;

                if (! $user) {
                    return;
                }

                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();

                Notification::make()
                    ->title('Compte valide')
                    ->success()
                    ->send();
            });
    }
}
