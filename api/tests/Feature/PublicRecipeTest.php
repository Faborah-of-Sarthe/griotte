<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class PublicRecipeTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_publish_rend_la_recette_publique_et_genere_un_token(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create(['is_public' => false, 'public_token' => null]);

        $response = $this->postJson("/api/recipes/{$recipe->id}/publish")
            ->assertOk()
            ->assertJsonPath('is_public', true);

        $token = $response->json('public_token');
        $this->assertNotEmpty($token);

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'is_public' => true,
            'public_token' => $token,
        ]);
    }

    public function test_publish_conserve_le_token_existant(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->public()->create();
        $token_initial = $recipe->public_token;

        $recipe->unpublish();

        $this->postJson("/api/recipes/{$recipe->id}/publish")
            ->assertOk()
            ->assertJsonPath('public_token', $token_initial);
    }

    public function test_unpublish_revoque_l_acces_public_sans_supprimer_le_token(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->public()->create();
        $token = $recipe->public_token;

        $this->deleteJson("/api/recipes/{$recipe->id}/publish")
            ->assertOk()
            ->assertJsonPath('is_public', false)
            ->assertJsonPath('public_token', $token);

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'is_public' => false,
            'public_token' => $token,
        ]);
    }

    public function test_publish_refuse_une_recette_d_un_autre_utilisateur(): void
    {
        $this->authentifier();
        $autre_user = User::factory()->create();
        $recipe = Recipe::factory()->for($autre_user)->create();

        $this->postJson("/api/recipes/{$recipe->id}/publish")
            ->assertForbidden()
            ->assertJsonPath('message', 'This resource does not belong to the authenticated user.');

        $this->assertDatabaseHas('recipes', [
            'id' => $recipe->id,
            'is_public' => false,
        ]);
    }

    public function test_acces_public_a_une_recette_publiee_renvoie_les_donnees_filtrees(): void
    {
        $owner = User::factory()->create();
        $recipe = Recipe::factory()->for($owner)->public()->create([
            'name' => 'Tarte aux pommes',
            'description' => 'Étaler la pâte',
            'link' => 'https://example.test/tarte',
        ]);
        $tag = Tag::factory()->for($owner)->create(['name' => 'Dessert']);
        $recipe->tags()->attach($tag->id);
        $product = Product::factory()->for($owner)->create([
            'name' => 'Pomme',
            'to_buy' => true,
            'comment' => 'note privée',
        ]);
        $recipe->products()->attach($product->id, ['quantity' => '4']);

        $response = $this->getJson("/api/public/recipes/{$recipe->public_token}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Tarte aux pommes')
            ->assertJsonPath('data.description', 'Étaler la pâte')
            ->assertJsonPath('data.link', 'https://example.test/tarte')
            ->assertJsonPath('data.products.0.name', 'Pomme')
            ->assertJsonPath('data.products.0.quantity', '4')
            ->assertJsonPath('data.tags.0.name', 'Dessert');

        // Aucune donnée privée ne doit fuiter.
        $contenu = $response->getContent();
        $this->assertStringNotContainsString('user_id', $contenu);
        $this->assertStringNotContainsString('to_make', $contenu);
        $this->assertStringNotContainsString('to_buy', $contenu);
        $this->assertStringNotContainsString('public_token', $contenu);
        $this->assertStringNotContainsString('note privée', $contenu);
    }

    public function test_acces_public_a_une_recette_non_publiee_renvoie_404(): void
    {
        $owner = User::factory()->create();
        $recipe = Recipe::factory()->for($owner)->create([
            'is_public' => false,
            'public_token' => 'jeton-non-publie',
        ]);

        $this->getJson("/api/public/recipes/{$recipe->public_token}")
            ->assertNotFound();
    }

    public function test_acces_public_a_un_token_inconnu_renvoie_404(): void
    {
        $this->getJson('/api/public/recipes/token-inexistant')
            ->assertNotFound();
    }

    public function test_acces_public_ne_necessite_pas_d_authentification(): void
    {
        $owner = User::factory()->create();
        $recipe = Recipe::factory()->for($owner)->public()->create();

        $this->getJson("/api/public/recipes/{$recipe->public_token}")
            ->assertOk();
    }
}
