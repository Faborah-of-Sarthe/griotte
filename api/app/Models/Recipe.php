<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'link',
        'to_make',
        'is_public',
    ];

    protected $casts = [
        'to_make' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Only recipes explicitly published by their owner.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Publish the recipe and generate a share token if missing.
     */
    public function publish(): self
    {
        $this->is_public = true;

        if (empty($this->public_token)) {
            $this->public_token = (string) Str::uuid();
        }

        $this->save();

        return $this;
    }

    /**
     * Unpublish the recipe while keeping its token for later reuse.
     */
    public function unpublish(): self
    {
        $this->is_public = false;
        $this->save();

        return $this;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_recipe')->withPivot('quantity');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
