<?php

namespace App\Casts;

use App\Settings\UserSettings;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<UserSettings, UserSettings|array<string, mixed>|null>
 */
class UserSettingsCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): UserSettings
    {
        if ($value instanceof UserSettings) {
            return $value;
        }

        $donnees = is_string($value) ? json_decode($value, true) : $value;

        return UserSettings::fromArray(is_array($donnees) ? $donnees : null);
    }

    /**
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            $value = new UserSettings();
        }

        if (is_array($value)) {
            $value = UserSettings::fromArray($value);
        }

        if (!$value instanceof UserSettings) {
            throw new InvalidArgumentException('Les paramètres utilisateur doivent être un objet UserSettings.');
        }

        return [
            $key => json_encode($value->toArray(), JSON_THROW_ON_ERROR),
        ];
    }
}
