<?php

namespace App\Providers;

use Laravel\Fortify\Fortify;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                $user = $request->user();
                return $request->wantsJson()
                            ? response()->json([
                                'two_factor' => false,
                                'finished_tutorial' => (bool) $user->finished_tutorial,
                                'email' => $user->email,
                                ])
                            : redirect()->intended(Fortify::redirects('login'));
            }
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
