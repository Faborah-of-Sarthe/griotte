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
            ->assertJsonPath('product.to_buy', 1)
            ->assertJsonPath('user.finished_tutorial', 1);

        $product = Product::where('name', 'Bananes')->first();

        $this->assertNotNull($product);
        $this->assertSame($user->id, $product->user_id);
        $this->assertTrue($section->products()->where('products.id', $product->id)->exists());
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
}
