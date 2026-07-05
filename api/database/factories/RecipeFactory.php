<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipe>
 */
class RecipeFactory extends Factory
{
    protected $model = Recipe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'description' => fake()->sentence(),
            'link' => fake()->optional()->url(),
            'user_id' => User::factory(),
            'to_make' => fake()->boolean(),
            'is_public' => false,
            'public_token' => null,
        ];
    }

    /**
     * State for a published (publicly shareable) recipe.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'public_token' => (string) Str::uuid(),
        ]);
    }
}
