<?php

namespace App\Models;

use App\Casts\UserSettingsCast;
use App\Notifications\CustomVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName as FilamentHasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentHasName, FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'is_admin',
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
            'settings' => UserSettingsCast::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_admin;
    }

    public function getFilamentName(): string
    {
        return $this->email ?? 'Utilisateur #'.$this->getKey();
    }

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

    /**
     * Get all the tags belonging to this user.
     */
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }
}
