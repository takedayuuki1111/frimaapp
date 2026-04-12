<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'postal_code',
        'address',
        'building_name',
        'avatar_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function soldItems()
    {
        return $this->hasMany(SoldItem::class);
    }

    public function sellingTransactions()
    {
        return $this->hasManyThrough(SoldItem::class, Item::class, 'user_id', 'item_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function receivedRatings()
    {
        $sellerRatings = $this->sellingTransactions()
            ->whereNotNull('seller_rating')
            ->pluck('seller_rating');

        $buyerRatings = $this->soldItems()
            ->whereNotNull('buyer_rating')
            ->pluck('buyer_rating');

        return $sellerRatings->merge($buyerRatings);
    }

    public function averageRating(): ?float
    {
        $ratings = $this->receivedRatings();

        if ($ratings->isEmpty()) {
            return null;
        }

        return (float) round($ratings->avg());
    }

    public function ratingCount(): int
    {
        return $this->receivedRatings()->count();
    }
}