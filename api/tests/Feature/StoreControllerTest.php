<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Section;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_index_retourne_uniquement_les_magasins_de_l_utilisateur(): void
    {
        $user = $this->authentifier();
        $store = Store::factory()->for($user)->create(['name' => 'Mon magasin']);
        Store::factory()->for(User::factory()->create())->create(['name' => 'Magasin étranger']);

        $this->getJson('/api/stores')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $store->id, 'name' => 'Mon magasin'])
            ->assertJsonMissing(['name' => 'Magasin étranger']);
    }

    public function test_store_cree_un_magasin_avec_section_par_defaut_et_le_definit_comme_courant(): void
    {
        $user = $this->authentifier();

        $this->postJson('/api/stores', ['name' => 'Carrefour'])
            ->assertCreated()
            ->assertJsonPath('message', 'Magasin créé avec succès.');

        $store = Store::where('name', 'Carrefour')->first();

        $this->assertNotNull($store);
        $this->assertSame($store->id, $user->fresh()->current_store);
        $this->assertSame(1, $store->sections()->count());
        $this->assertSame('Fruits & Légumes', $store->sections()->first()->name);
    }

    public function test_store_copie_les_sections_et_les_produits_d_un_magasin_source(): void
    {
        $user = $this->authentifier();
        $source = Store::factory()->for($user)->create();
        $section_source = Section::factory()->for($source)->create([
            'name' => 'Epicerie',
            'color' => 3,
            'icon' => 'bag',
            'order' => 4,
        ]);
        $product = Product::factory()->for($user)->create(['name' => 'Pâtes']);
        $section_source->products()->attach($product->id);

        $this->postJson('/api/stores', [
            'name' => 'Magasin copié',
            'copyFrom' => $source->id,
        ])
            ->assertCreated()
            ->assertJsonPath('store.name', 'Magasin copié');

        $store = Store::where('name', 'Magasin copié')->first();
        $section_copiee = $store->sections()->first();

        $this->assertSame('Epicerie', $section_copiee->name);
        $this->assertSame(3, $section_copiee->color);
        $this->assertSame('bag', $section_copiee->icon);
        $this->assertSame(4, $section_copiee->order);
        $this->assertTrue($section_copiee->products()->where('products.id', $product->id)->exists());
    }

    public function test_destroy_refuse_de_supprimer_le_magasin_courant(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);

        $this->deleteJson("/api/stores/{$store->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Vous ne pouvez pas supprimer votre magasin actuel.');

        $this->assertDatabaseHas('stores', ['id' => $store->id]);
    }

    public function test_update_sections_order_refuse_une_section_hors_du_magasin(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $autre_store = Store::factory()->for($user)->create();
        $section = Section::factory()->for($autre_store)->create(['order' => 1]);

        $this->putJson("/api/stores/{$store->id}/sections/reorder", [
            'sections' => [
                ['id' => $section->id, 'order' => 2],
            ],
        ])
            ->assertForbidden()
            ->assertJsonPath('message', "Le rayon n'appartient pas au magasin actuel.");

        $this->assertSame(1, $section->fresh()->order);
    }

    public function test_update_current_store_et_get_current_store(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $nouveau_store = Store::factory()->for($user)->create();

        $this->putJson("/api/stores/{$nouveau_store->id}/set-current")
            ->assertOk()
            ->assertJsonPath('message', 'Magasin bien sélectionné !')
            ->assertJsonPath('store.id', $nouveau_store->id);

        $this->assertSame($nouveau_store->id, $user->fresh()->current_store);
        $this->assertNotSame($store->id, $user->fresh()->current_store);

        $response = $this->getJson('/api/stores/current')->assertOk();

        $this->assertSame((string) $nouveau_store->id, $response->getContent());
    }
}
