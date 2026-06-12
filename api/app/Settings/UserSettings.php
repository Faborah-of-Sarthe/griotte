<?php

namespace App\Settings;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class UserSettings implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly bool $unclassified_first = false,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        $regles = [];

        foreach (UserSettingKey::cases() as $setting_key) {
            $regles[$setting_key->value] = 'sometimes|' . $setting_key->regleValidation();
        }

        return $regles;
    }

    public static function fromValue(mixed $valeur): self
    {
        if ($valeur instanceof self) {
            return $valeur;
        }

        if (is_string($valeur)) {
            $donnees = json_decode($valeur, true);

            return self::fromArray(is_array($donnees) ? $donnees : null);
        }

        if (is_array($valeur)) {
            return self::fromArray($valeur);
        }

        return self::fromArray(null);
    }

    /**
     * @param array<string, mixed>|null $donnees
     */
    public static function fromArray(?array $donnees): self
    {
        $donnees ??= [];
        $valeurs = [];

        foreach (UserSettingKey::cases() as $setting_key) {
            $valeur = array_key_exists($setting_key->value, $donnees)
                ? $donnees[$setting_key->value]
                : $setting_key->valeurParDefaut();

            $valeurs[$setting_key->value] = $setting_key->normaliser($valeur);
        }

        return new self(...$valeurs);
    }

    /**
     * @param array<string, mixed> $donnees
     */
    public function with(array $donnees): self
    {
        return self::fromArray([
            ...$this->toArray(),
            ...$donnees,
        ]);
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        $donnees = [];

        foreach (UserSettingKey::cases() as $setting_key) {
            $donnees[$setting_key->value] = $this->{$setting_key->value};
        }

        return $donnees;
    }

    /**
     * @return array<string, bool>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
