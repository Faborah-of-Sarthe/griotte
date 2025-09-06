<?php

namespace App\Services;

use DOMXPath;
use DOMDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class RecipeImportService
{
    /**
     * Extract recipe data from a URL using microdata and JSON-LD
     */
    public function extractRecipeFromUrl($url)
    {
        try {
            // Utiliser Guzzle au lieu de file_get_contents
            $client = new Client([
                'timeout' => 30,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                ]
            ]);

            $response = $client->get($url);
            $html = $response->getBody()->getContents();

            if (!$html) {
                throw new \Exception('Impossible de récupérer le contenu de la page');
            }

        } catch (RequestException $e) {
            throw new \Exception('Erreur lors de la récupération de la page : ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Impossible de récupérer le contenu de la page : ' . $e->getMessage());
        }

        // Créer un document DOM
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // Essayer d'abord de trouver des données JSON-LD
        $jsonLdData = $this->extractJsonLd($xpath);
        if ($jsonLdData) {
            return $this->processRecipeData($jsonLdData, $url);
        }

        // Si pas de JSON-LD, essayer les micro-données
        $microdataRecipe = $this->extractMicrodata($xpath);
        if ($microdataRecipe) {
            return $this->processRecipeData($microdataRecipe, $url);
        }

        return null;
    }

    /**
     * Extract recipe data from JSON-LD
     */
    private function extractJsonLd($xpath)
    {
        $scripts = $xpath->query('//script[@type="application/ld+json"]');

        foreach ($scripts as $script) {
            $json = json_decode($script->textContent, true);

            if (!$json) continue;

            // Gérer les cas où c'est un array ou un objet unique
            $items = is_array($json) && isset($json[0]) ? $json : [$json];

            foreach ($items as $item) {
                if (isset($item['@type']) && $item['@type'] === 'Recipe') {
                    return $item;
                }

                // Rechercher dans un objet Graph
                if (isset($item['@graph'])) {
                    foreach ($item['@graph'] as $graphItem) {
                        if (isset($graphItem['@type']) && $graphItem['@type'] === 'Recipe') {
                            return $graphItem;
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract recipe data from microdata
     */
    private function extractMicrodata($xpath)
    {
        // Chercher un élément avec itemtype Recipe
        $recipeElements = $xpath->query('//*[@itemtype="http://schema.org/Recipe" or @itemtype="https://schema.org/Recipe"]');

        if ($recipeElements->length === 0) {
            return null;
        }

        $recipeElement = $recipeElements->item(0);
        $recipe = [];

        // Extraire le nom
        $nameElements = $xpath->query('.//*[@itemprop="name"]', $recipeElement);
        if ($nameElements->length > 0) {
            $recipe['name'] = trim($nameElements->item(0)->textContent);
        }

        // Extraire les instructions
        $instructionElements = $xpath->query('.//*[@itemprop="recipeInstructions" or @itemprop="instructions"]', $recipeElement);
        $instructions = [];
        foreach ($instructionElements as $element) {
            $instructions[] = trim($element->textContent);
        }
        $recipe['recipeInstructions'] = $instructions;

        // Extraire les ingrédients
        $ingredientElements = $xpath->query('.//*[@itemprop="recipeIngredient"]', $recipeElement);
        $ingredients = [];
        foreach ($ingredientElements as $element) {
            $ingredients[] = trim($element->textContent);
        }
        $recipe['recipeIngredient'] = $ingredients;

        return $recipe;
    }

    /**
     * Process and normalize recipe data
     */
    private function processRecipeData($recipeData, $originalUrl)
    {
        // Extraire le nom
        $name = $recipeData['name'] ?? 'Recette importée';

        // Extraire et traiter les instructions
        $instructions = [];
        if (isset($recipeData['recipeInstructions'])) {
            if (is_array($recipeData['recipeInstructions'])) {
                foreach ($recipeData['recipeInstructions'] as $instruction) {
                    if (is_string($instruction)) {
                        $instructions[] = $instruction;
                    } elseif (is_array($instruction) && isset($instruction['text'])) {
                        $instructions[] = $instruction['text'];
                    }
                }
            }
        }

        $description = implode("\n\n", $instructions);

        return [
            'name' => $name,
            'description' => $description,
            'originalUrl' => $originalUrl,
            'rawRecipeData' => $recipeData
        ];
    }

    /**
     * Parse ingredients from recipe data for a specific user
     */
    public function parseIngredientsForUser($recipeData, User $user)
    {
        $ingredients = [];

        if (isset($recipeData['recipeIngredient']) && is_array($recipeData['recipeIngredient'])) {
            foreach ($recipeData['recipeIngredient'] as $ingredient) {
                $parsedIngredient = $this->parseIngredient($ingredient, $user);
                if ($parsedIngredient && $parsedIngredient['name']) {
                    $ingredients[] = $parsedIngredient;
                }
            }
        }

        return $ingredients;
    }

    /**
     * Parse an ingredient string and match with existing products
     */
    public function parseIngredient($ingredientText, User $user)
    {
        // Nettoyer le texte de l'ingrédient
        $cleaned = trim($ingredientText);
        if (empty($cleaned)) {
            return null;
        }

        // Patterns pour séparer quantité et nom d'ingrédient
        $patterns = [
            // "70 g Farine" ou "70 g de Farine"
            '/^([0-9]+(?:[,\.][0-9]+)?\s*(?:g|kg|ml|cl|l|mg))\s*(?:de\s+)?\s*(.+)$/i',
            // "4 c. à soupe Huile d'olive" ou "2 cuillères à soupe de sucre"
            '/^([0-9]+(?:[,\.][0-9]+)?\s*(?:c\.|cuillères?|cuillère)\s*(?:à\s+(?:soupe|café|thé))?)\s*(?:de\s+)?\s*(.+)$/i',
            // "1/2 tasse de farine" ou "3/4 verre d'eau"
            '/^([0-9]+\/[0-9]+\s*(?:tasse|verre|bol)s?)\s*(?:de?\s+)?\s*(.+)$/i',
            // "2 tranches de pain" ou "3 gousses d'ail"
            '/^([0-9]+(?:[,\.][0-9]+)?\s*(?:tranches?|gousses?|branches?|feuilles?|pincées?))\s*(?:de?\s+)?\s*(.+)$/i',
            // "Une pincée de sel" ou "Un peu de poivre"
            '/^((?:une?|quelques?)\s*(?:pincées?|peu))\s*(?:de\s+)?\s*(.+)$/i',
            // Pattern pour nombre + nom au pluriel (ex: "2 oeufs")
            '/^([0-9]+\s+(?:petite?s?\s+)?)\s*([a-zÀ-ÿ]+(?:s|x)?)\s*$/ui',
            // Pattern général pour nombre + unité + "de" optionnel
            '/^([0-9]+(?:[,\.][0-9]+)?\s*(?:g|kg|ml|cl|l|mg|c\.|cuillères?|cuillère|tasse|verre|bol|tranches?|gousses?|branches?|feuilles?|pincées?))\s*(?:de\s+)?\s*(.+)$/i',
            // Pattern très simple pour tout ce qui commence par un chiffre
            '/^([0-9]+[^a-zA-ZÀ-ÿ]*)\s*(.+)$/u'
        ];

        $quantity = '';
        $name = $cleaned;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleaned, $matches)) {
                $quantity = trim($matches[1]);
                $name = trim($matches[2]);
                break;
            }
        }

        // Nettoyer le nom pour la recherche
        $searchName = $this->cleanIngredientName($name);

        // Chercher des produits similaires
        $existingProduct = $this->findSimilarProduct($searchName, $user);

        return [
            'name' => $searchName,
            'quantity' => $quantity,
            'existingProduct' => $existingProduct,
            'originalText' => $cleaned
        ];
    }

    /**
     * Clean ingredient name for search
     */
    private function cleanIngredientName($name)
    {
        // Supprimer les parenthèses et leur contenu
        $cleaned = preg_replace('/\([^)]*\)/', '', $name);

        // Supprimer les crochets et leur contenu
        $cleaned = preg_replace('/\[[^\]]*\]/', '', $cleaned);

        // Supprimer les prépositions courantes en début
        $cleaned = preg_replace('/^(?:de\s+|du\s+|des\s+|d\'\s*)/i', '', $cleaned);

        // Supprimer les mots courants de préparation
        $wordsToRemove = [
            'frais', 'fraîche', 'fraîches', 'fraîchement',
            'sec', 'sèche', 'sèches', 'séché', 'séchée', 'séchées',
            'en conserve', 'en boîte', 'en bocal',
            'surgelé', 'surgelés', 'surgelée', 'surgelées',
            'haché', 'hachée', 'hachés', 'hachées', 'finement haché',
            'coupé', 'coupée', 'coupés', 'coupées', 'en morceaux', 'en dés',
            'râpé', 'râpée', 'râpés', 'râpées',
            'émincé', 'émincée', 'émincés', 'émincées',
            'pelé', 'pelée', 'pelés', 'pelées', 'épluché', 'épluchée',
            'sans peau', 'sans noyau', 'dénoyauté', 'dénoyautée',
            'bio', 'biologique', 'extra', 'fine', 'gros', 'grosse'
        ];

        foreach ($wordsToRemove as $word) {
            // Supprimer en début ou fin de chaîne ou entouré d'espaces/virgules
            $cleaned = preg_replace('/(?:^|[\s,]+)' . preg_quote($word) . '(?=[\s,]|$)/i', '', $cleaned);
        }

        // Supprimer les virgules orphelines et les espaces multiples
        $cleaned = preg_replace('/\s*,\s*,\s*/', ', ', $cleaned); // virgules multiples
        $cleaned = preg_replace('/^,\s*|,\s*$/', '', $cleaned);    // virgules en début/fin
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);          // espaces multiples
        $cleaned = trim($cleaned);

        // Capitaliser le premier caractère
        return ucfirst($cleaned);
    }

    /**
     * Find similar product in user's products (handling singular/plural variations)
     */
    private function findSimilarProduct($ingredientName, User $user)
    {
        $searchName = strtolower($ingredientName);

        // 1. Recherche exacte et variations en une seule requête
        $variations = $this->generateNameVariations($searchName);
        $allExactTerms = array_merge([$searchName], $variations);

        $exactMatch = $user->products()
            ->whereIn(DB::raw('LOWER(name)'), $allExactTerms)
            ->orderByRaw('CASE WHEN LOWER(name) = ? THEN 0 ELSE 1 END', [$searchName])
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // 2. Recherche partielle avec scoring
        $partialMatch = $user->products()
            ->selectRaw('*,
                CASE
                    WHEN LOWER(name) LIKE ? THEN 1
                    WHEN LOWER(name) LIKE ? THEN 2
                    WHEN LOWER(name) LIKE ? THEN 3
                    ELSE 4
                END as match_score', [
                    $searchName . '%',     // commence par
                    '%' . $searchName,     // finit par
                    '%' . $searchName . '%' // contient
                ])
            ->whereRaw('LOWER(name) LIKE ?', ['%' . $searchName . '%'])
            ->orderBy('match_score')
            ->orderBy('name')
            ->first();

        return $partialMatch;
    }

    /**
     * Generate singular/plural variations of a name
     */
    private function generateNameVariations($name)
    {
        $variations = [];

        // Variations singulier/pluriel existantes
        if (substr($name, -1) === 's') {
            $variations[] = substr($name, 0, -1);
        } else {
            $variations[] = $name . 's';
        }

        // Nouvelles variations
        if (substr($name, -3) === 'eau' || substr($name, -2) === 'eu') {
            $variations[] = $name . 'x';
        }

        // Variations avec accents
        $accentMap = [
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'î' => 'i', 'ï' => 'i',
            'ô' => 'o', 'ö' => 'o',
            'ç' => 'c'
        ];

        $withoutAccents = strtr($name, $accentMap);
        if ($withoutAccents !== $name) {
            $variations[] = $withoutAccents;
            $variations[] = $withoutAccents . 's';
        }

        return array_unique($variations);
    }
}
