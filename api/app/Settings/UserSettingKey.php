<?php

namespace App\Settings;

enum UserSettingKey: string
{
    /**
     * Affiche les produits non classés avant les rayons dans la liste.
     */
    case UnclassifiedFirst = 'unclassified_first';

    /**
     * Garde l'écran allumé quand la liste de courses est affichée.
     */
    case KeepScreenAwake = 'keep_screen_awake';

    public function type(): string
    {
        return match ($this) {
            self::UnclassifiedFirst,
            self::KeepScreenAwake => 'boolean',
        };
    }

    public function valeurParDefaut(): mixed
    {
        return match ($this) {
            self::UnclassifiedFirst,
            self::KeepScreenAwake => false,
        };
    }

    public function normaliser(mixed $valeur): mixed
    {
        return match ($this->type()) {
            'boolean' => $this->normaliserBooleen($valeur),
        };
    }

    public function regleValidation(): string
    {
        return match ($this->type()) {
            'boolean' => 'boolean',
        };
    }

    private function normaliserBooleen(mixed $valeur): bool
    {
        if (is_bool($valeur)) {
            return $valeur;
        }

        return filter_var($valeur, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ?? $this->valeurParDefaut();
    }
}
