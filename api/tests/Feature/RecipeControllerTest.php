<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class RecipeControllerTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_index_filtre_les_recettes_a_preparer_et_la_recherche(): void
    {
        $user = $this->authentifier();
        $pizza = Recipe::factory()->for($user)->create(['name' => 'Pizza maison', 'to_make' => true]);
        Recipe::factory()->for($user)->create(['name' => 'Pizza froide', 'to_make' => false]);
        Recipe::factory()->for($user)->create(['name' => 'Soupe', 'to_make' => true]);
        Recipe::factory()->for(User::factory()->create())->create(['name' => 'Pizza étrangère', 'to_make' => true]);

        $this->getJson('/api/recipes?choice=to_make&search=Pizza')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $pizza->id)
            ->assertJsonPath('data.0.name', 'Pizza maison');
    }

    public function test_index_pagine_les_recettes_par_dix(): void
    {
        $user = $this->authentifier();
        Recipe::factory()->count(11)->for($user)->create();

        $this->getJson('/api/recipes')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('total', 11);
    }

    public function test_count_retourne_le_nombre_de_recettes_a_preparer(): void
    {
        $user = $this->authentifier();
        Recipe::factory()->count(2)->for($user)->create(['to_make' => true]);
        Recipe::factory()->for($user)->create(['to_make' => false]);

        $response = $this->getJson('/api/recipes/count')->assertOk();

        $this->assertSame('2', $response->getContent());
    }

    public function test_store_cree_une_recette_non_preparee(): void
    {
        $user = $this->authentifier();

        $this->postJson('/api/recipes', [
            'name' => 'Crêpes',
            'description' => 'Mélanger puis cuire',
            'link' => 'https://example.test/crepes',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Crêpes')
            ->assertJsonPath('to_make', false);

        $this->assertDatabaseHas('recipes', [
            'user_id' => $user->id,
            'name' => 'Crêpes',
            'to_make' => false,
        ]);
    }

    public function test_update_modifie_une_recette_et_refuse_une_requete_vide(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create(['to_make' => false]);

        $this->patchJson("/api/recipes/{$recipe->id}", [])
            ->assertUnprocessable();

        $this->patchJson("/api/recipes/{$recipe->id}", [
            'name' => 'Risotto',
            'to_make' => true,
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Risotto')
            ->assertJsonPath('to_make', true);
    }

    public function test_attach_product_attache_un_produit_existant_de_l_utilisateur(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();
        $product = Product::factory()->for($user)->create();

        $this->postJson("/api/recipes/{$recipe->id}/products", [
            'product_id' => $product->id,
        ])->assertOk();

        $this->assertDatabaseHas('product_recipe', [
            'recipe_id' => $recipe->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_attach_product_cree_un_nouveau_produit_si_aucun_produit_existant_n_est_fourni(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();

        $this->postJson("/api/recipes/{$recipe->id}/products", [
            'name' => 'farine',
        ])->assertOk();

        $product = Product::where('user_id', $user->id)->where('name', 'Farine')->first();

        $this->assertNotNull($product);
        $this->assertFalse((bool) $product->to_buy);
        $this->assertTrue($recipe->products()->where('products.id', $product->id)->exists());
    }

    public function test_attach_product_refuse_un_produit_d_un_autre_utilisateur(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();
        $product = Product::factory()->for(User::factory()->create())->create();

        $this->postJson("/api/recipes/{$recipe->id}/products", [
            'product_id' => $product->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_detach_product_retire_un_ingredient_de_la_recette(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();
        $product = Product::factory()->for($user)->create();
        $recipe->products()->attach($product->id);

        $this->deleteJson("/api/recipes/{$recipe->id}/products/{$product->id}")
            ->assertOk();

        $this->assertDatabaseMissing('product_recipe', [
            'recipe_id' => $recipe->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_update_product_quantity_modifie_la_quantite_du_pivot(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();
        $product = Product::factory()->for($user)->create();
        $recipe->products()->attach($product->id, ['quantity' => '1']);

        $this->patchJson("/api/recipes/{$recipe->id}/products/{$product->id}", [
            'quantity' => '200 g',
        ])->assertOk();

        $this->assertDatabaseHas('product_recipe', [
            'recipe_id' => $recipe->id,
            'product_id' => $product->id,
            'quantity' => '200 g',
        ]);
    }

    public function test_add_ingredient_to_shopping_list_met_a_jour_le_produit(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create(['name' => 'Gâteau']);
        $product = Product::factory()->for($user)->create([
            'name' => 'Farine',
            'to_buy' => false,
            'comment' => 'Déjà noté',
        ]);
        $recipe->products()->attach($product->id, ['quantity' => '200 g']);

        $this->postJson("/api/recipes/{$recipe->id}/products/{$product->id}/add-to-list")
            ->assertOk()
            ->assertJsonPath('message', 'Farine ajouté à la liste de courses avec succès');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'to_buy' => true,
            'comment' => "Déjà noté\n- Gâteau ( 200 g )",
        ]);
    }

    public function test_add_ingredient_to_shopping_list_refuse_un_ingredient_absent_de_la_recette(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();
        $product = Product::factory()->for($user)->create();

        $this->postJson("/api/recipes/{$recipe->id}/products/{$product->id}/add-to-list")
            ->assertNotFound()
            ->assertJsonPath('message', 'Cet ingrédient ne fait pas partie de cette recette');
    }

    public function test_add_all_ingredients_to_shopping_list_refuse_une_recette_vide(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();

        $this->postJson("/api/recipes/{$recipe->id}/add-all-to-list")
            ->assertNotFound()
            ->assertJsonPath('message', 'Cette recette ne contient aucun ingrédient');
    }

    public function test_add_all_ingredients_to_shopping_list_ajoute_tous_les_ingredients(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create(['name' => 'Salade']);
        $tomate = Product::factory()->for($user)->create(['name' => 'Tomate', 'to_buy' => false, 'comment' => null]);
        $concombre = Product::factory()->for($user)->create(['name' => 'Concombre', 'to_buy' => false, 'comment' => null]);
        $recipe->products()->attach($tomate->id, ['quantity' => '2']);
        $recipe->products()->attach($concombre->id, ['quantity' => '1']);

        $this->postJson("/api/recipes/{$recipe->id}/add-all-to-list")
            ->assertOk()
            ->assertJsonPath('added_count', 2)
            ->assertJsonPath('skipped_count', 0)
            ->assertJsonPath('message', '2 ingrédients ajoutés à la liste de courses avec succès');

        $this->assertSame('- Salade ( 2 )', $tomate->fresh()->comment);
        $this->assertSame('- Salade ( 1 )', $concombre->fresh()->comment);
    }

    public function test_create_from_import_cree_la_recette_et_ses_ingredients(): void
    {
        $user = $this->authentifier();
        $lait = Product::factory()->for($user)->create(['name' => 'Lait']);

        $this->postJson('/api/recipes/create-from-import', [
            'name' => 'Pancakes',
            'description' => 'Tout mélanger',
            'link' => 'https://example.test/pancakes',
            'ingredients' => [
                [
                    'name' => 'Lait',
                    'quantity' => '25 cl',
                    'existingProduct' => ['id' => $lait->id, 'name' => 'Lait'],
                ],
                [
                    'name' => 'sucre',
                    'quantity' => '50 g',
                ],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Pancakes')
            ->assertJsonCount(2, 'products');

        $recipe = Recipe::where('name', 'Pancakes')->first();
        $sucre = Product::where('user_id', $user->id)->where('name', 'Sucre')->first();

        $this->assertNotNull($recipe);
        $this->assertNotNull($sucre);
        $this->assertDatabaseHas('product_recipe', [
            'recipe_id' => $recipe->id,
            'product_id' => $lait->id,
            'quantity' => '25 cl',
        ]);
        $this->assertDatabaseHas('product_recipe', [
            'recipe_id' => $recipe->id,
            'product_id' => $sucre->id,
            'quantity' => '50 g',
        ]);
    }
}
