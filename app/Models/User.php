<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->shoppingCarts()->create(['name' => 'Môj košík', 'is_default' => true]);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'gdpr_consent_at',
        'gdpr_consent_ip',
        'terms_accepted_at',
        'terms_accepted_ip',
        'marketing_consent',
        'marketing_consent_at',
        'home_shortcuts',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => UserRole::class,
            'password' => 'hashed',
            'gdpr_consent_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'marketing_consent_at' => 'datetime',
            'home_shortcuts' => 'array',
        ];
    }

    public function isInternal(): bool
    {
        return $this->role->isInternal();
    }

    public function favoriteProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'favorite_products')->withTimestamps();
    }

    public function shoppingCarts(): HasMany
    {
        return $this->hasMany(ShoppingCart::class)->orderBy('id');
    }
}
