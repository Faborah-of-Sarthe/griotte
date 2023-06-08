<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Product;
use App\Models\Section;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create();

        for ($i=0; $i < 1; $i++) { 
            $store = Store::factory()->for($user)->create();
            for ($j=0; $j < 5; $j++) { 
                $section = Section::factory()->for($store)->create();
                for ($k=0; $k < 5; $k++) { 
                    $product = Product::factory()->for($user)->create();
                    $product->section()->attach($section);
                }
            }
        }
    }
}
