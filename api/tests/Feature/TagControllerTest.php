<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesGriotteData;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use CreatesGriotteData;
    use RefreshDatabase;

    public function test_index_retourne_uniquement_les_tags_de_l_utilisateur(): void
    {
        $user = $this->authentifier();
        $tag = Tag::factory()->for($user)->create(['name' => 'Végétarien']);
        Tag::factory()->for(User::factory()->create())->create(['name' => 'Autre']);

        $this->getJson('/api/tags')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $tag->id)
            ->assertJsonPath('0.name', 'Végétarien');
    }

    public function test_store_cree_un_tag_unique_pour_l_utilisateur(): void
    {
        $user = $this->authentifier();

        $this->postJson('/api/tags', [
            'name' => 'Kid-friendly',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Kid-friendly');

        $this->assertDatabaseHas('tags', [
            'user_id' => $user->id,
            'name' => 'Kid-friendly',
        ]);
    }

    public function test_store_refuse_un_nom_deja_utilise_par_le_meme_utilisateur(): void
    {
        $user = $this->authentifier();
        Tag::factory()->for($user)->create(['name' => 'Rapide']);
        Tag::factory()->for(User::factory()->create())->create(['name' => 'Rapide']);

        $this->postJson('/api/tags', [
            'name' => 'Rapide',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_renomme_un_tag_de_l_utilisateur(): void
    {
        $user = $this->authentifier();
        $tag = Tag::factory()->for($user)->create(['name' => 'Ancien']);

        $this->patchJson("/api/tags/{$tag->id}", [
            'name' => 'Nouveau',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Nouveau');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Nouveau',
        ]);
    }

    public function test_update_refuse_un_tag_d_un_autre_utilisateur(): void
    {
        $this->authentifier();
        $tag = Tag::factory()->for(User::factory()->create())->create();

        $this->patchJson("/api/tags/{$tag->id}", [
            'name' => 'Interdit',
        ])->assertForbidden();
    }

    public function test_destroy_supprime_le_tag_et_ses_associations(): void
    {
        $user = $this->authentifier();
        $recipe = Recipe::factory()->for($user)->create();
        $tag = Tag::factory()->for($user)->create();
        $recipe->tags()->attach($tag->id);

        $this->deleteJson("/api/tags/{$tag->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Tag supprimé avec succès.');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
        $this->assertDatabaseMissing('recipe_tag', [
            'recipe_id' => $recipe->id,
            'tag_id' => $tag->id,
        ]);
    }
}
