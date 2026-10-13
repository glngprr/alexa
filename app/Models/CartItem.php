<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Get the cart that owns the item.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product variant for the item.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Calculate the item subtotal based on current database price.
     */
    public function subtotal(): int
    {
        if (!$this->productVariant) {
            return 0;
        }

        return (int) ($this->productVariant->price * $this->quantity);
    }

    /**
     * Determine if the product and variant are active and available.
     */
    public function isAvailable(): bool
    {
        return $this->productVariant
            && $this->productVariant->status === 'active'
            && $this->productVariant->product
            && $this->productVariant->product->status === 'active';
    }

    /**
     * Determine if the requested quantity exceeds the current variant stock.
     */
    public function isStockExceeded(): bool
    {
        if (!$this->productVariant) {
            return true;
        }

        return $this->quantity > $this->productVariant->stock;
    }

    /**
     * Determine if this cart item is valid and purchasable.
     */
    public function isPurchasable(): bool
    {
        return $this->isAvailable() && !$this->isStockExceeded();
    }
}
