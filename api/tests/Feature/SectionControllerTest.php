<?php

namespace Tests\Feature;

use App\Models\Section;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class SectionControllerTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_index_retourne_les_sections_du_magasin_courant_ordonnees(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $section_deuxieme = Section::factory()->for($store)->create(['name' => 'Deuxième', 'order' => 2]);
        $section_premiere = Section::factory()->for($store)->create(['name' => 'Première', 'order' => 1]);
        Section::factory()->for(Store::factory()->for($user))->create(['name' => 'Autre magasin', 'order' => 0]);

        $this->getJson('/api/sections')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.id', $section_premiere->id)
            ->assertJsonPath('1.id', $section_deuxieme->id)
            ->assertJsonMissing(['name' => 'Autre magasin']);
    }

    public function test_index_retourne_404_sans_magasin_courant(): void
    {
        $this->authentifier();

        $this->getJson('/api/sections')
            ->assertNotFound()
            ->assertJsonPath('message', "Aucun magasin n'a été sélectionné par l'utilisateur.");
    }

    public function test_store_cree_une_section_pour_un_magasin_de_l_utilisateur(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);

        $this->postJson('/api/sections', [
            'name' => 'Boulangerie',
            'color' => 5,
            'icon' => 'bread',
            'store_id' => $store->id,
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Section created successfully.')
            ->assertJsonPath('section.name', 'Boulangerie');

        $this->assertDatabaseHas('sections', [
            'name' => 'Boulangerie',
            'color' => 5,
            'icon' => 'bread',
            'store_id' => $store->id,
        ]);
    }

    public function test_store_refuse_un_magasin_d_un_autre_utilisateur(): void
    {
        $this->authentifier();
        $autre_store = Store::factory()->for(User::factory()->create())->create();

        $this->postJson('/api/sections', [
            'name' => 'Boulangerie',
            'color' => 5,
            'icon' => 'bread',
            'store_id' => $autre_store->id,
        ])
            ->assertForbidden()
            ->assertJsonPath('message', "Le magasin donné n'appartient pas à l'utilisateur connecté.");
    }

    public function test_store_valide_les_champs_obligatoires(): void
    {
        $this->authentifier();

        $this->postJson('/api/sections', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'color', 'icon', 'store_id']);
    }

    public function test_update_modifie_une_section(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $section = Section::factory()->for($store)->create();

        $this->patchJson("/api/sections/{$section->id}", [
            'name' => 'Frais',
            'color' => 2,
            'icon' => 'snowflake',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'Rayon mis à jour avec succès.')
            ->assertJsonPath('section.name', 'Frais');

        $this->assertDatabaseHas('sections', [
            'id' => $section->id,
            'name' => 'Frais',
            'color' => 2,
            'icon' => 'snowflake',
        ]);
    }

    public function test_destroy_supprime_une_section(): void
    {
        [$user, $store] = $this->creer_magasin_courant();
        $this->authentifier($user);
        $section = Section::factory()->for($store)->create();

        $this->deleteJson("/api/sections/{$section->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Rayon supprimé avec succès.');

        $this->assertDatabaseMissing('sections', ['id' => $section->id]);
    }
}
