<?php

namespace App\Filament\Infolists\Components;

use App\Support\MaskedValue;
use Closure;
use Filament\Infolists\Components\TextEntry;

class MaskedTextEntry extends TextEntry
{
    protected bool|Closure $is_email = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->formatStateUsing(fn (mixed $state): mixed => MaskedValue::render(
            value: $state,
            is_email: (bool) $this->evaluate($this->is_email),
        ));
    }

    public function email(bool|Closure $condition = true): static
    {
        $this->is_email = $condition;

        return $this;
    }
}
