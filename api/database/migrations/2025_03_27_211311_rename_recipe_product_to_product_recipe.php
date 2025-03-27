<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('recipe_product', 'product_recipe');

        DB::statement('ALTER TABLE product_recipe MODIFY quantity integer DEFAULT 1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE product_recipe MODIFY quantity integer DEFAULT NULL');

        Schema::rename('product_recipe', 'recipe_product');
    }
};
