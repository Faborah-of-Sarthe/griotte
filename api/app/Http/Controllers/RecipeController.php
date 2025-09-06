<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Product;
use App\Services\RecipeImportService;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    private $recipeImportService;

    public function __construct(RecipeImportService $recipeImportService)
    {
        $this->recipeImportService = $recipeImportService;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        return $user
            ->recipes()
            ->when($request->has('choice') && $request->choice === 'to_make', function ($query) {
                return $query->where('to_make', true);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                return $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function show(Recipe $recipe)
    {
        return $recipe->load('products');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'link' => 'nullable|string|max:255',
        ]);

        $validated['to_make'] = false;

        $recipe = auth('sanctum')->user()->recipes()->create($validated);

        return $recipe;
    }

    public function update(Recipe $recipe, Request $request)
    {
        $validated = $request->validate([
            'to_make' => 'sometimes|boolean',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:65535',
            'link' => 'sometimes|nullable|string|max:255',
        ]);

        if (empty($validated)) {
            abort(422, 'Au moins un champ doit être fourni');
        }

        $recipe->fill($validated)->save();

        return $recipe;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recipe $recipe)
    {
        $recipe->delete();

        return response()->json([
            'message' => 'Recette supprimée avec succès.'
        ], 200);
    }

    /**
     * Count the number of recipes to make
     *
     * @return int
     */
    public function count(Request $request)
    {
        $user = auth('sanctum')->user();
        return $user->recipes()->where('to_make', true)->count();
    }

    public function attachProduct(Recipe $recipe, Request $request)
    {
        $validated = $request->validate([
            'product_id' => [
                'nullable',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $user = auth('sanctum')->user();
                    if (!$user->products()->where('id', $value)->exists()) {
                        $fail('Le produit sélectionné ne vous appartient pas.');
                    }
                }
            ],
            'name' => 'required_if:product_id,null|string|max:255',
        ]);

        if ($validated['product_id']) {
            $recipe->products()->syncWithoutDetaching($validated['product_id']);
        } else {
            $recipe->products()->create([
                'name' => ucfirst($validated['name']),
                'user_id' => auth('sanctum')->user()->id,
            ]);
        }

        return $recipe;
    }

    public function detachProduct(Recipe $recipe, $productId)
    {
        $recipe->products()->detach($productId);
        return $recipe->load('products');
    }

    public function updateProductQuantity(Recipe $recipe, $productId, Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|string',
        ]);

        $recipe->products()->updateExistingPivot($productId, [
            'quantity' => $validated['quantity']
        ]);

        return $recipe->load('products');
    }

    /**
     * Add an ingredient of the recipe to the shopping list
     */
    public function addIngredientToShoppingList(Recipe $recipe, $productId)
    {
        $product = auth('sanctum')->user()->products()->findOrFail($productId);
        $pivotData = $recipe->products()->where('product_id', $productId)->first();

        if (!$pivotData) {
            return response()->json(['message' => 'Cet ingrédient ne fait pas partie de cette recette'], 404);
        }

        $this->addProductToShoppingList($product, $recipe, $pivotData->pivot->quantity ?? null);

        return response()->json([
            'message' => $product->name . ' ajouté à la liste de courses avec succès',
            'options' => [
                'timeout' => 1500,
            ],
            'product' => $product
        ]);
    }

    /**
     * Add all ingredients of the recipe to the shopping list
     */
    public function addAllIngredientsToShoppingList(Recipe $recipe)
    {
        $user = auth('sanctum')->user();
        $products = $recipe->products;

        if ($products->isEmpty()) {
            return response()->json(['message' => 'Cette recette ne contient aucun ingrédient'], 404);
        }

        $addedCount = 0;
        $skippedCount = 0;

        foreach ($products as $recipeProduct) {
            $this->addProductToShoppingList($recipeProduct, $recipe, $recipeProduct->pivot->quantity ?? null);
            $addedCount++;
        }

        $message = $addedCount . ' ingrédient' . ($addedCount > 1 ? 's' : '') . ' ajouté' . ($addedCount > 1 ? 's' : '') . ' à la liste de courses avec succès';

        if ($skippedCount > 0) {
            $message .= ' (' . $skippedCount . ' ingrédient' . ($skippedCount > 1 ? 's' : '') . ' ignoré' . ($skippedCount > 1 ? 's' : '') . ')';
        }

        return response()->json([
            'message' => $message,
            'options' => [
                'timeout' => 1500,
            ],
            'added_count' => $addedCount,
            'skipped_count' => $skippedCount
        ]);
    }


    /**
     * Parse a recipe URL to extract recipe data using microdata
     */
    public function parseUrl(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url'
        ]);

        try {
            $recipeData = $this->recipeImportService->extractRecipeFromUrl($validated['url']);

            if (!$recipeData) {
                return response()->json([
                    'message' => 'Impossible d\'extraire les données de recette depuis cette URL. Vérifiez que la page contient bien une recette avec des micro-données.'
                ], 422);
            }

            // Traiter les ingrédients pour l'utilisateur connecté
            $user = auth('sanctum')->user();
            $ingredients = $this->recipeImportService->parseIngredientsForUser($recipeData['rawRecipeData'], $user);
            $recipeData['ingredients'] = $ingredients;

            // Supprimer les données brutes avant de retourner la réponse
            unset($recipeData['rawRecipeData']);

            return response()->json($recipeData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'analyse de la page : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a recipe from imported data
     */
    public function createFromImport(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:65535',
            'link' => 'nullable|string|max:255',
            'ingredients' => 'sometimes|array',
            'ingredients.*.name' => 'required_with:ingredients|string|max:255',
            'ingredients.*.quantity' => 'nullable|string',
            'ingredients.*.existingProduct' => 'sometimes|nullable'
        ]);

        $user = auth('sanctum')->user();

        // Créer la recette
        $recipe = $user->recipes()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'link' => $validated['link'] ?? '',
            'to_make' => false
        ]);

        // Traiter les ingrédients
        if (isset($validated['ingredients'])) {
            foreach ($validated['ingredients'] as $ingredientData) {
                $productId = null;

                if (isset($ingredientData['existingProduct']) && $ingredientData['existingProduct']) {
                    // Utiliser le produit existant
                    $productId = $ingredientData['existingProduct']['id'];
                } else {
                    // Créer un nouveau produit
                    $product = $user->products()->create([
                        'name' => ucfirst($ingredientData['name']),
                        'to_buy' => false,
                        'comment' => ''
                    ]);
                    $productId = $product->id;
                }

                // Attacher le produit à la recette si on a un ID
                if ($productId) {
                    $recipe->products()->attach($productId, [
                        'quantity' => $ingredientData['quantity'] ?? null
                    ]);
                }
            }
        }

        return $recipe->load('products');
    }


    /**
     * Private method to add a product to the shopping list
     */
    private function addProductToShoppingList($product, Recipe $recipe, $quantity = null)
    {
        // Build the new comment line
        $newCommentLine = $recipe->name;
        if ($quantity) {
            $newCommentLine .= ' ( ' . $quantity . ' )';
        }

        // Add the new line to the existing comment
        $currentComment = $product->comment ? $product->comment : '';
        $updatedComment = $currentComment ? $currentComment . "\n- " . $newCommentLine : "- " . $newCommentLine;

        $product->update([
            'to_buy' => 1,
            'comment' => $updatedComment
        ]);
    }
}
