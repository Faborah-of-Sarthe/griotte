<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as Middleware;

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
