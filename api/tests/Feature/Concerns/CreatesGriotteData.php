<?php

namespace Tests\Feature\Concerns;

use App\Models\Store;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

trait CreatesGriotteData
{
    protected function authentifier(?User $user = null): User
    {
        $user ??= User::factory()->create();

        Sanctum::actingAs($user);

        return $user;
    }

    protected function creer_magasin_courant(?User $user = null, array $attributs = []): array
    {
        $user ??= User::factory()->create();
        $store = Store::factory()->for($user)->create($attributs);

        $user->forceFill(['current_store' => $store->id])->save();

        return [$user->fresh(), $store];
    }
}
