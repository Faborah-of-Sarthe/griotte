<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class MaskedValue
{
    public static function render(mixed $value, bool $is_email = false): HtmlString
    {
        $real_value = self::stringValue($value);

        return new HtmlString(view('filament.components.masked-value', [
            'value' => $real_value,
            'masked_value' => self::mask($real_value, $is_email),
        ])->render());
    }

    public static function mask(?string $value, bool $is_email = false): string
    {
        if (blank($value)) {
            return 'Non renseigne';
        }

        if ($is_email && str_contains($value, '@')) {
            [$local_part, $domain] = explode('@', $value, 2);
            $extension = str_contains($domain, '.') ? '.' . str($domain)->afterLast('.') : '';

            return mb_substr($local_part, 0, 1) . '***@***' . $extension;
        }

        $length = max(6, min(16, mb_strlen($value)));

        return str_repeat('*', $length);
    }

    private static function stringValue(mixed $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return (string) $value;
    }
}
