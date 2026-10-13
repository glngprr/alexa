<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Alias for cart items.
     */
    public function items(): HasMany
    {
        return $this->cartItems();
    }

    /**
     * Calculate total price of the cart based on current database prices.
     */
    public function total(): int
    {
        return (int) $this->cartItems->sum(function (CartItem $item) {
            return $item->subtotal();
        });
    }

    /**
     * Get the total count of items in the cart.
     */
    public function itemsCount(): int
    {
        return (int) $this->cartItems->sum('quantity');
    }

    /**
     * Determine if any item in the cart has stock or availability issues.
     */
    public function hasIssues(): bool
    {
        return $this->cartItems->contains(function (CartItem $item) {
            return !$item->isPurchasable();
        });
    }
}
