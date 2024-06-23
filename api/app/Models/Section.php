<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'icon',
        // 'store_id',
    ];

    /**
     * Hide unnecessary fields from the JSON response
     */
    protected $hidden = [
        'pivot', 'store_id', 'created_at', 'updated_at'
    ];

    /**
     * Get the store this section belongs to
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the products in this section
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public static function firstSection(Store $store)
    {
        // Create the first section
        $section = new Section();
        $section->name = 'Fruits & Légumes';
        $section->color = 4;
        $section->icon = 'fruits-legumes';
        $section->store_id = $store->id;
        $section->save();


        $products = [];

        $names = ['Pommes', 'Poires', 'Bananes', 'Oranges', 'Citrons', 'Mangues', 'Ananas', 'Fraises', 'Cerises', 'Pêches', 'Abricots', 'Prunes', 'Raisins', 'Kiwi', 'Melon', 'Pastèque', 'Pommes de terre', 'Carottes', 'Tomates', 'Concombres', 'Salade', 'Choux', 'Poireaux', 'Ail', 'Oignons', 'Echalotes', 'Aubergines', 'Courgettes', 'Poivrons', 'Haricots verts', 'Petits pois', 'Radis', 'Betteraves', 'Céleris', 'Champignons', 'Endives', 'Courges', 'Panais', 'Navets', 'Topinambours', 'Choux de Bruxelles', 'Artichauts', 'Asperges', 'Brocolis', 'Choux-fleurs', 'Potirons', 'Potimarrons', 'Butternuts', 'Patates douces', 'Châtaignes', 'Pak Choy', 'Chou chinois', 'Pousses de soja', 'gingembre'];

        $existingProducts = Product::where('user_id', $store->user_id)->whereIn('name', $names)->get();
        $toAttach = [];
        foreach ($names as $name) {

            if($existingProducts->contains('name', $name)) {
                // If the product already exists, save the id
                $toAttach[] = $existingProducts->where('name', $name)->first()->id;
            } else {
                // If the product doesn't exist, create it
                if($name == 'Cerises') {
                    $products[] =  new Product([
                        'name' => 'Cerises',
                        'to_buy' => true,
                        'comment' => now()->month >= 5 && now()->month <= 7 ? 'Parfait c\'est la saison !' : 'Bon, on est un peu hors saison là...',
                        'user_id' => $store->user_id,
                    ]);
                } else {
                    $products[] = new Product([
                        'name' => $name,
                        'to_buy' => false,
                        'comment' => '',
                        'user_id' => $store->user_id,
                    ]);
                }
            }
        }

        // Create a list of products
        $section->products()->saveMany($products);

        // Attach the existing products
        $section->products()->attach($toAttach);

        return $section;

    }

}
