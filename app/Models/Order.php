<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'order_number',
        'shipping_address_id',
        'subtotal',
        'shipping_cost',
        'total',
        'order_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'total' => 'integer',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    /**
     * Get the user that placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipping address for the order.
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get the items in the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Alias for order items.
     */
    public function items(): HasMany
    {
        return $this->orderItems();
    }

    /**
     * Get the latest payment for the order.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Get all payments for the order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the current payment status.
     */
    public function paymentStatus(): string
    {
        return $this->payment?->status ?? 'pending';
    }

    /**
     * Determine if the order can be paid.
     */
    public function isEligibleForPayment(): bool
    {
        return $this->order_status === 'pending' && $this->paymentStatus() !== 'paid';
    }

    /**
     * Get the latest shipment for the order.
     */
    public function shipment()
    {
        return $this->hasOne(Shipment::class)->latestOfMany();
    }

    /**
     * Get all shipments for the order.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Get the current shipment status.
     */
    public function shipmentStatus(): string
    {
        return $this->shipment?->status ?? 'pending';
    }

    /**
     * Determine if the order is eligible to create a shipment.
     * Must be paid and have no active shipment.
     */
    public function canBeShipped(): bool
    {
        $isPaid = $this->paymentStatus() === 'paid';
        $hasActiveShipment = $this->shipment && in_array($this->shipment->status, ['pending', 'shipped', 'in_transit', 'delivered']);

        return $isPaid && !$hasActiveShipment;
    }
}
