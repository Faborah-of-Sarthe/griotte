<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Public, read-only view of a recipe.
 *
 * Exposes only the fields that are safe to share publicly and deliberately
 * omits private data such as user_id, to_make, the share token, and the
 * shopping-list state of the ingredients (to_buy, comment).
 */
class PublicRecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'link' => $this->link,
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(function ($product) {
                    return [
                        'name' => $product->name,
                        'quantity' => $product->pivot->quantity ?? null,
                    ];
                })->values();
            }),
            'tags' => $this->whenLoaded('tags', function () {
                return $this->tags->map(fn ($tag) => [
                    'name' => $tag->name,
                ])->values();
            }),
        ];
    }
}
