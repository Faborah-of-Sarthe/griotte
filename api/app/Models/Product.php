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
     * Get all the sections where this product is located
     */
    public function section()
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

    /** 
     * Hide the pivot table from the JSON response
     */
     
    protected $hidden = [
        'pivot'
    ];
}
