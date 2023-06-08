<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_id',
    ];
    
    /**
     * Get all the sections belonging to this store.
     */
    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    /**
     * Get the user this store belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
