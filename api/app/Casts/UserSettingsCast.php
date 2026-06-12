<?php

namespace App\Casts;

use App\Settings\UserSettings;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<UserSettings, UserSettings|array<string, mixed>|null>
 */
class UserSettingsCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): UserSettings
    {
        return UserSettings::fromValue($value);
    }

    /**
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $value = UserSettings::fromValue($value);

        return [
            $key => json_encode($value->toArray(), JSON_THROW_ON_ERROR),
        ];
    }
}
