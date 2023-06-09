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
        'store_id',
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
}
