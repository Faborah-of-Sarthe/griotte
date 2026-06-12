<?php

namespace App\Settings;

enum UserSettingKey: string
{
    /**
     * Whether to display the unclassified products first in the product list.
     */
    case UnclassifiedFirst = 'unclassified_first';

    public function type(): string
    {
        return match ($this) {
            self::UnclassifiedFirst => 'boolean',
        };
    }

    public function valeurParDefaut(): mixed
    {
        return match ($this) {
            self::UnclassifiedFirst => false,
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
