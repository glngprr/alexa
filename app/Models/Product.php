<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'collection_id',
        'name',
        'slug',
        'description',
        'capacity',
        'material',
        'lid_type',
        'weight',
        'status',
    ];

    /**
     * Get the collection that owns the product.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get all variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the active variants for the product.
     */
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('status', 'active');
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Get the default variant for the product (first active variant).
     */
    public function defaultVariant(): ?ProductVariant
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->firstWhere('status', 'active') ?? $this->variants->first();
        }

        return $this->activeVariants()->first() ?? $this->variants()->first();
    }

    /**
     * Get all in-stock active variants for the product.
     */
    public function inStockVariants(): EloquentCollection
    {
        if ($this->relationLoaded('variants')) {
            return $this->variants->filter(function (ProductVariant $variant) {
                return $variant->status === 'active' && $variant->isInStock();
            })->values();
        }

        return $this->activeVariants()->where('stock', '>', 0)->get();
    }
}
