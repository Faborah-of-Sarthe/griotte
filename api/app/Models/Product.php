<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'to_buy',
        'comment',
        'user_id',
    ];

    /**
     * Hide unnecessary fields from the JSON response
     */
    protected $hidden = [
        'pivot', 'user_id', 'created_at', 'updated_at'
    ];

    /**
     * Load the sections of the product for the current store
     */
    public function loadSections()
    {
        $store = auth('sanctum')->user()->currentStore;
        $this->sections = $this->sections()->where('store_id', $store->id)->get();
    }

    /**
     * Get all the sections where this product is located
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class);
    }

    /**
     * Get the user this product belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
