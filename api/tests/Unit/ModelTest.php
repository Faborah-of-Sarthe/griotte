<?php

namespace Tests\Unit;

use App\Filament\Resources\Users\UserResource;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Section;
use App\Models\Store;
use App\Models\Tag;
use App\Models\User;
use App\Settings\UserSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_expose_ses_relations_metier(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->for($user)->create();
        $product = Product::factory()->for($user)->create();
        $recipe = Recipe::factory()->for($user)->create();
        $tag = Tag::factory()->for($user)->create();
        $recipe->tags()->attach($tag->id);

        $user->forceFill(['current_store' => $store->id])->save();

        $this->assertTrue($user->stores->contains($store));
        $this->assertTrue($user->products->contains($product));
        $this->assertTrue($user->recipes->contains($recipe));
        $this->assertTrue($user->tags->contains($tag));
        $this->assertTrue($recipe->tags->contains($tag));
        $this->assertTrue($store->is($user->currentStore));
    }

    public function test_user_resource_protege_l_admin_connecte(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->assertFalse(UserResource::canDelete($admin));
        $this->assertFalse(UserResource::canChangeAdminStatus($admin));
    }

    public function test_user_resource_protege_le_dernier_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $autre_admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($autre_admin);

        $this->assertTrue(UserResource::canDelete($admin));
        $this->assertTrue(UserResource::canChangeAdminStatus($admin));

        $autre_admin->forceFill(['is_admin' => false])->save();

        $this->assertFalse(UserResource::canDelete($admin));
        $this->assertFalse(UserResource::canChangeAdminStatus($admin));
    }

    /**
     * Test that the user settings are normalized from a JSON string by adding the default values.
     */
    public function test_user_settings_normalise_une_chaine_json(): void
    {
        $settings = UserSettings::fromValue('{"unclassified_first":true}');

        $this->assertTrue($settings->unclassified_first);
        $this->assertSame([
            'unclassified_first' => true,
            'keep_screen_awake' => false,
        ], $settings->toArray());
    }

    public function test_section_first_section_cree_la_section_et_les_produits_par_defaut(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 12));

        $user = User::factory()->create();
        $store = Store::factory()->for($user)->create();

        $section = Section::firstSection($store);

        $this->assertSame('Fruits & Légumes', $section->name);
        $this->assertSame(4, $section->color);
        $this->assertSame('0-cerises', $section->icon);
        $this->assertSame($store->id, $section->store_id);
        $this->assertGreaterThan(40, $section->products()->count());

        $cerises = Product::where('user_id', $user->id)->where('name', 'Cerises')->first();

        $this->assertNotNull($cerises);
        $this->assertTrue((bool) $cerises->to_buy);
        $this->assertSame('Parfait c\'est la saison !', $cerises->comment);

        Carbon::setTestNow();
    }

    public function test_section_first_section_reutilise_les_produits_existants(): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->for($user)->create();
        $pommes = Product::factory()->for($user)->create(['name' => 'Pommes']);

        $section = Section::firstSection($store);

        $this->assertTrue($section->products()->where('products.id', $pommes->id)->exists());
        $this->assertSame(1, Product::where('user_id', $user->id)->where('name', 'Pommes')->count());
    }

    public function test_get_front_url_depend_de_l_environnement(): void
    {
        config()->set('app.front_url', 'griotte.test');

        config()->set('app.env', 'local');
        $this->assertSame('http://griotte.test', getFrontUrl());

        config()->set('app.env', 'production');
        $this->assertSame('https://griotte.test', getFrontUrl());
    }
}
