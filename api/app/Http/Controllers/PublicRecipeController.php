<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicRecipeResource;
use App\Models\Recipe;

class PublicRecipeController extends Controller
{
    /**
     * Display a published recipe from its public share token.
     *
     * This endpoint is intentionally unauthenticated. A missing or unpublished
     * recipe returns a 404 so the existence of private recipes is never leaked.
     */
    public function show(string $token)
    {
        $recipe = Recipe::query()
            ->public()
            ->where('public_token', $token)
            ->with(['products', 'tags'])
            ->firstOrFail();

        return new PublicRecipeResource($recipe);
    }
}
