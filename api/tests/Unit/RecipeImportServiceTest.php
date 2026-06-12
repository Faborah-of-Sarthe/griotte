<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\User;
use App\Services\RecipeImportService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parse_ingredient_separe_les_quantites_et_les_noms(): void
    {
        $user = User::factory()->create();
        $service = new RecipeImportService();

        $cas = [
            ['70 g de Farine', '70 g', 'Farine'],
            ["4 c. à soupe Huile d'olive", '4 c. à soupe', "Huile d'olive"],
            ['1/2 tasse de farine', '1/2 tasse', 'Farine'],
            ["3 gousses d'ail", '3 gousses', 'Ail'],
            ['Une pincée de sel', 'Une pincée', 'Sel'],
            ['2 oeufs', '2', 'Oeufs'],
            ['Persil', '', 'Persil'],
        ];

        foreach ($cas as [$texte, $quantite, $nom]) {
            $ingredient = $service->parseIngredient($texte, $user);

            $this->assertSame($quantite, $ingredient['quantity']);
            $this->assertSame($nom, $ingredient['name']);
            $this->assertSame($texte, $ingredient['originalText']);
        }
    }

    public function test_parse_ingredient_nettoie_le_nom_de_l_ingredient(): void
    {
        $user = User::factory()->create();
        $service = new RecipeImportService();

        $ingredient = $service->parseIngredient('200 g de tomates fraîches (pelées), bio', $user);

        $this->assertSame('200 g', $ingredient['quantity']);
        $this->assertSame('Tomates', $ingredient['name']);
    }

    public function test_parse_ingredient_associe_un_produit_existant_exact_ou_variable(): void
    {
        $user = User::factory()->create();
        $oeuf = Product::factory()->for($user)->create(['name' => 'Oeuf']);
        $echalote = Product::factory()->for($user)->create(['name' => 'Echalote']);
        Product::factory()->create(['name' => 'Oeufs']);

        $service = new RecipeImportService();

        $ingredient_oeufs = $service->parseIngredient('2 oeufs', $user);
        $ingredient_echalote = $service->parseIngredient('1 échalote', $user);

        $this->assertTrue($oeuf->is($ingredient_oeufs['existingProduct']));
        $this->assertTrue($echalote->is($ingredient_echalote['existingProduct']));
    }

    public function test_parse_ingredient_associe_un_produit_existant_par_recherche_partielle(): void
    {
        $user = User::factory()->create();
        $tomates = Product::factory()->for($user)->create(['name' => 'Tomates cerises']);

        $service = new RecipeImportService();

        $ingredient = $service->parseIngredient('100 g de tomates', $user);

        $this->assertTrue($tomates->is($ingredient['existingProduct']));
    }

    public function test_parse_ingredient_ne_renvoie_pas_de_produit_si_aucun_resultat(): void
    {
        $user = User::factory()->create();
        $service = new RecipeImportService();

        $ingredient = $service->parseIngredient('100 g de farine', $user);

        $this->assertNull($ingredient['existingProduct']);
    }

    public function test_parse_ingredients_for_user_ignore_les_ingredients_vides(): void
    {
        $user = User::factory()->create();
        $service = new RecipeImportService();

        $ingredients = $service->parseIngredientsForUser([
            'recipeIngredient' => ['  ', '120 g de sucre'],
        ], $user);

        $this->assertCount(1, $ingredients);
        $this->assertSame('Sucre', $ingredients[0]['name']);
    }

    public function test_extract_recipe_from_url_lit_les_donnees_json_ld(): void
    {
        $html = <<<'HTML'
        <html>
            <head>
                <script type="application/ld+json">
                    {
                        "@context": "https://schema.org",
                        "@type": "Recipe",
                        "name": "Tarte aux pommes",
                        "recipeInstructions": [
                            {"@type": "HowToStep", "text": "Couper les pommes"},
                            {"@type": "HowToStep", "text": "Cuire la tarte"}
                        ],
                        "recipeIngredient": ["3 pommes", "100 g de farine"]
                    }
                </script>
            </head>
        </html>
        HTML;

        $service = $this->service_avec_html($html);

        $recette = $service->extractRecipeFromUrl('https://example.test/recette');

        $this->assertSame('Tarte aux pommes', $recette['name']);
        $this->assertSame("Couper les pommes\n\nCuire la tarte", $recette['description']);
        $this->assertSame('https://example.test/recette', $recette['originalUrl']);
        $this->assertSame(['3 pommes', '100 g de farine'], $recette['rawRecipeData']['recipeIngredient']);
    }

    public function test_extract_recipe_from_url_lit_les_microdonnees(): void
    {
        $html = <<<'HTML'
        <div itemscope itemtype="https://schema.org/Recipe">
            <h1 itemprop="name">Soupe maison</h1>
            <p itemprop="recipeInstructions">Mixer les légumes</p>
            <span itemprop="recipeIngredient">2 carottes</span>
        </div>
        HTML;

        $service = $this->service_avec_html($html);

        $recette = $service->extractRecipeFromUrl('https://example.test/soupe');

        $this->assertSame('Soupe maison', $recette['name']);
        $this->assertSame('Mixer les légumes', $recette['description']);
        $this->assertSame(['2 carottes'], $recette['rawRecipeData']['recipeIngredient']);
    }

    public function test_extract_recipe_from_url_renvoie_null_sans_donnees_recette(): void
    {
        $service = $this->service_avec_html('<html><body>Pas de recette</body></html>');

        $recette = $service->extractRecipeFromUrl('https://example.test/page');

        $this->assertNull($recette);
    }

    private function service_avec_html(string $html): RecipeImportService
    {
        $mock = new MockHandler([
            new Response(200, [], $html),
        ]);

        return new RecipeImportService(new Client([
            'handler' => HandlerStack::create($mock),
        ]));
    }
}
