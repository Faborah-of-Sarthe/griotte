<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\Section;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class OwnershipTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_un_utilisateur_non_authentifie_est_refuse(): void
    {
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson('/api/stores')
            ->assertUnauthorized();
    }

    public function test_un_store_d_un_autre_utilisateur_est_refuse(): void
    {
        $user = $this->authentifier();
        $autre_user = User::factory()->create();
        $store = Store::factory()->for($autre_user)->create();

        $this->getJson("/api/stores/{$store->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'This resource does not belong to the authenticated user.');

        $this->assertSame($user->id, auth('sanctum')->user()->id);
    }

    public function test_un_produit_d_un_autre_utilisateur_est_refuse(): void
    {
        $this->authentifier();
        $autre_user = User::factory()->create();
        $product = Product::factory()->for($autre_user)->create();

        $this->patchJson("/api/products/{$product->id}", ['name' => 'Lait'])
            ->assertForbidden()
            ->assertJsonPath('message', 'This resource does not belong to the authenticated user.');
    }

    public function test_une_section_d_un_store_d_un_autre_utilisateur_est_refusee(): void
    {
        $this->authentifier();
        $autre_user = User::factory()->create();
        $store = Store::factory()->for($autre_user)->create();
        $section = Section::factory()->for($store)->create();

        $this->patchJson("/api/sections/{$section->id}", [
            'name' => 'Frais',
            'color' => 1,
            'icon' => 'snowflake',
        ])
            ->assertForbidden()
            ->assertJsonPath('message', 'This resource does not belong to the authenticated user.');
    }

    public function test_une_recette_d_un_autre_utilisateur_est_refusee(): void
    {
        $this->authentifier();
        $autre_user = User::factory()->create();
        $recipe = Recipe::factory()->for($autre_user)->create();

        $this->getJson("/api/recipes/{$recipe->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'This resource does not belong to the authenticated user.');
    }
}
