<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Section;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_index_retourne_les_produits_a_acheter_par_sections_et_non_classes(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);

        $section_lente = Section::factory()->for($store)->create(['name' => 'Surgelés', 'order' => 2]);
        $section_rapide = Section::factory()->for($store)->create(['name' => 'Frais', 'order' => 1]);
        $autre_store = Store::factory()->for($user)->create();
        $section_autre_store = Section::factory()->for($autre_store)->create();

        $produit_classe = Product::factory()->for($user)->create(['name' => 'Lait', 'to_buy' => true]);
        $produit_ignore = Product::factory()->for($user)->create(['name' => 'Yaourt', 'to_buy' => false]);
        $produit_sans_section = Product::factory()->for($user)->create(['name' => 'Pain', 'to_buy' => true]);
        $produit_hors_store = Product::factory()->for($user)->create(['name' => 'Riz', 'to_buy' => true]);

        $section_rapide->products()->attach([$produit_classe->id, $produit_ignore->id]);
        $section_autre_store->products()->attach($produit_hors_store->id);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('0.id', $section_rapide->id)
            ->assertJsonPath('1.id', $section_lente->id)
            ->assertJsonPath('2.id', 0)
            ->assertJsonPath('2.name', 'Non classé')
            ->assertJsonFragment(['name' => 'Lait'])
            ->assertJsonFragment(['name' => 'Pain'])
            ->assertJsonFragment(['name' => 'Riz'])
            ->assertJsonMissing(['name' => 'Yaourt']);

        $this->assertTrue($produit_sans_section->exists);
    }

    public function test_index_positionne_les_produits_non_classes_avant_les_rayons_selon_la_preference(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $user->settings = ['unclassified_first' => true];
        $user->save();
        $this->authentifier($user->fresh());

        $section_lente = Section::factory()->for($store)->create(['name' => 'Surgelés', 'order' => 2]);
        $section_rapide = Section::factory()->for($store)->create(['name' => 'Frais', 'order' => 1]);

        $produit_classe = Product::factory()->for($user)->create(['name' => 'Lait', 'to_buy' => true]);
        $produit_sans_section = Product::factory()->for($user)->create(['name' => 'Pain', 'to_buy' => true]);

        $section_rapide->products()->attach($produit_classe->id);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('0.id', 0)
            ->assertJsonPath('0.name', 'Non classé')
            ->assertJsonPath('1.id', $section_rapide->id)
            ->assertJsonPath('2.id', $section_lente->id)
            ->assertJsonFragment(['name' => 'Pain'])
            ->assertJsonFragment(['name' => 'Lait']);

        $this->assertTrue($produit_sans_section->exists);
    }

    public function test_index_retourne_404_si_aucun_magasin_courant(): void
    {
        $user = $this->authentifier();

        $this->getJson('/api/products')
            ->assertNotFound()
            ->assertJsonPath('message', "'Aucun magasin sélectionné. Veuillez en sélectionner un dans la liste de vos magasins.'");

        $this->assertNull($user->current_store);
    }

    public function test_store_cree_un_produit_a_acheter_et_termine_le_tutoriel(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $section = Section::factory()->for($store)->create();

        $this->postJson('/api/products', [
            'name' => 'bananes',
            'comment' => 'Pour le goûter',
            'section_id' => $section->id,
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Produit créé avec succès.')
            ->assertJsonPath('product.name', 'Bananes')
            ->assertJsonPath('product.to_buy', true)
            ->assertJsonPath('product.is_temporary', false)
            ->assertJsonPath('user.finished_tutorial', 1);

        $product = Product::where('name', 'Bananes')->first();

        $this->assertNotNull($product);
        $this->assertSame($user->id, $product->user_id);
        $this->assertTrue($section->products()->where('products.id', $product->id)->exists());
    }

    public function test_store_cree_un_produit_temporaire_a_acheter(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);

        $this->postJson('/api/products', [
            'name' => 'pile bouton',
            'is_temporary' => true,
        ])
            ->assertCreated()
            ->assertJsonPath('product.name', 'Pile bouton')
            ->assertJsonPath('product.to_buy', true)
            ->assertJsonPath('product.is_temporary', true);

        $this->assertDatabaseHas('products', [
            'name' => 'Pile bouton',
            'user_id' => $user->id,
            'to_buy' => true,
            'is_temporary' => true,
        ]);
    }

    public function test_index_conserve_les_produits_temporaires_coches_recents(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);

        // The deletion of checked temporary products is handled on the frontend
        // once the undo window is over, so the API must keep recent ones.
        $produit_temporaire_coche = Product::factory()->for($user)->create([
            'name' => 'Temporaire coché',
            'to_buy' => false,
            'is_temporary' => true,
        ]);

        $this->getJson('/api/products')
            ->assertOk()
            ->assertJsonMissing(['name' => 'Temporaire coché']);

        $this->assertDatabaseHas('products', ['id' => $produit_temporaire_coche->id]);
    }

    public function test_index_supprime_les_produits_temporaires_coches_orphelins(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);

        // Safety net: a temporary product checked off more than 24h ago (the
        // frontend never cleaned it up) is purged, while a temporary product
        // still "to buy" and a recent one are kept.
        $orphelin = Product::factory()->for($user)->create([
            'name' => 'Temporaire orphelin',
            'to_buy' => false,
            'is_temporary' => true,
            'updated_at' => now()->subDays(2),
        ]);
        $recent = Product::factory()->for($user)->create([
            'name' => 'Temporaire récent',
            'to_buy' => false,
            'is_temporary' => true,
        ]);
        $actif = Product::factory()->for($user)->create([
            'name' => 'Temporaire actif',
            'to_buy' => true,
            'is_temporary' => true,
            'updated_at' => now()->subDays(2),
        ]);

        $this->getJson('/api/products')->assertOk();

        $this->assertDatabaseMissing('products', ['id' => $orphelin->id]);
        $this->assertDatabaseHas('products', ['id' => $recent->id]);
        $this->assertDatabaseHas('products', ['id' => $actif->id]);
    }

    public function test_update_change_la_section_du_magasin_courant_et_garde_les_autres_magasins(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $ancienne_section = Section::factory()->for($store)->create();
        $nouvelle_section = Section::factory()->for($store)->create();
        $autre_store = Store::factory()->for($user)->create();
        $section_autre_store = Section::factory()->for($autre_store)->create();
        $product = Product::factory()->for($user)->create(['name' => 'lait', 'comment' => 'Bio']);

        $ancienne_section->products()->attach($product->id);
        $section_autre_store->products()->attach($product->id);

        $this->patchJson("/api/products/{$product->id}", [
            'name' => 'beurre',
            'section_id' => $nouvelle_section->id,
        ])
            ->assertCreated()
            ->assertJsonPath('product.name', 'Beurre');

        $this->assertFalse($ancienne_section->products()->where('products.id', $product->id)->exists());
        $this->assertTrue($nouvelle_section->products()->where('products.id', $product->id)->exists());
        $this->assertTrue($section_autre_store->products()->where('products.id', $product->id)->exists());
    }

    public function test_update_remet_le_produit_en_non_classe_quand_section_id_vaut_zero(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $ancienne_section = Section::factory()->for($store)->create();
        $autre_store = Store::factory()->for($user)->create();
        $section_autre_store = Section::factory()->for($autre_store)->create();
        $product = Product::factory()->for($user)->create(['name' => 'lait']);

        $ancienne_section->products()->attach($product->id);
        $section_autre_store->products()->attach($product->id);

        // 0 is the virtual "Non classé" section: it must be accepted and detach
        // the product from the current store sections without triggering the
        // "section_id sélectionné invalide" validation error.
        $this->patchJson("/api/products/{$product->id}", [
            'name' => 'beurre',
            'section_id' => 0,
        ])
            ->assertCreated()
            ->assertJsonPath('product.name', 'Beurre');

        $this->assertFalse($ancienne_section->products()->where('products.id', $product->id)->exists());
        $this->assertTrue($section_autre_store->products()->where('products.id', $product->id)->exists());
    }

    public function test_update_rejette_une_section_id_inexistante(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $product = Product::factory()->for($user)->create();

        $this->patchJson("/api/products/{$product->id}", [
            'section_id' => 999999,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('section_id');
    }

    public function test_update_efface_le_commentaire_quand_le_produit_est_coche(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $product = Product::factory()->for($user)->create([
            'to_buy' => true,
            'comment' => 'A prendre en promo',
        ]);

        $this->patchJson("/api/products/{$product->id}", ['to_buy' => false])
            ->assertCreated()
            ->assertJsonPath('product.comment', null);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'to_buy' => false,
            'comment' => null,
        ]);
    }

    public function test_destroy_supprime_le_produit(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $product = Product::factory()->for($user)->create();

        $this->deleteJson("/api/products/{$product->id}")
            ->assertCreated()
            ->assertJsonPath('message', 'Produit supprimé avec succès.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_attach_to_section_deplace_le_produit_dans_le_magasin_courant(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $ancienne_section = Section::factory()->for($store)->create();
        $nouvelle_section = Section::factory()->for($store)->create();
        $product = Product::factory()->for($user)->create();

        $ancienne_section->products()->attach($product->id);

        $this->putJson("/api/products/{$product->id}/set-section/{$nouvelle_section->id}")
            ->assertCreated()
            ->assertJsonPath('message', 'Produit bien ajouté au rayon.');

        $this->assertFalse($ancienne_section->products()->where('products.id', $product->id)->exists());
        $this->assertTrue($nouvelle_section->products()->where('products.id', $product->id)->exists());
    }

    public function test_autocomplete_limite_les_resultats_a_l_utilisateur_et_au_magasin_courant(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $section = Section::factory()->for($store)->create();

        for ($index = 1; $index <= 8; $index++) {
            $product = Product::factory()->for($user)->create(['name' => "Lait {$index}", 'comment' => 'masqué']);
            $section->products()->attach($product->id);
        }

        Product::factory()->for(User::factory()->create())->create(['name' => 'Lait autre']);

        $response = $this->getJson('/api/products/autocomplete?q=Lait')
            ->assertOk()
            ->assertJsonCount(7);

        $response->assertJsonMissing(['comment' => 'masqué']);
        $response->assertJsonFragment(['name' => 'Lait 1']);
        $response->assertJsonMissing(['name' => 'Lait autre']);
    }

    public function test_autocomplete_exclut_les_produits_temporaires(): void
    {
        [$user] = $this->creer_magasin_courant();
        $this->authentifier($user);

        Product::factory()->for($user)->create(['name' => 'Piles rechargeables', 'is_temporary' => false]);
        Product::factory()->for($user)->create(['name' => 'Piles bouton', 'is_temporary' => true]);

        $this->getJson('/api/products/autocomplete?q=Piles')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Piles rechargeables'])
            ->assertJsonMissing(['name' => 'Piles bouton']);
    }
}
