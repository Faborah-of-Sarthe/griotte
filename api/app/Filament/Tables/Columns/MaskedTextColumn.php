<?php

namespace App\Filament\Tables\Columns;

use App\Support\MaskedValue;
use Closure;
use Filament\Tables\Columns\TextColumn;

class MaskedTextColumn extends TextColumn
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
