<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class PreventRequestForgery extends Middleware
{
    /**
     * Les URI exclues de la verification CSRF.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}
