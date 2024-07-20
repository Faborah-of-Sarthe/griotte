<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get all the stores belonging to this user.
     */
    public function stores()
    {
        return $this->hasMany(Store::class);
    }

    /**
     * Get the current store of this user.
     */
    public function currentStore()
    {
        return $this->belongsTo(Store::class, 'current_store');
    }

    /**
     * Get all the products belonging to this user.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail);
    }

    /**
     * Get all the recipes belonging to this user.
     */
    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

}
