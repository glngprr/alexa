<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'provider',
        'biteship_order_id',
        'biteship_tracking_id',
        'waybill_id',
        'courier_code',
        'courier_service',
        'courier_name',
        'status',
        'tracking_url',
        'shipped_at',
        'delivered_at',
        'history',
        'payload',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'history' => 'array',
            'payload' => 'array',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Get the order associated with the shipment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if shipment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if shipment is in transit or shipped.
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, ['shipped', 'in_transit']);
    }

    /**
     * Check if shipment is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Determine if a status transition from current status to incoming status is valid and non-regressive.
     */
    public static function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        if ($currentStatus === $newStatus) {
            return true;
        }

        // Terminal states cannot regress to earlier active delivery states
        if ($currentStatus === 'delivered' && in_array($newStatus, ['pending', 'shipped', 'in_transit'])) {
            return false;
        }

        $ranks = [
            'pending' => 0,
            'shipped' => 1,
            'in_transit' => 2,
            'delivered' => 3,
        ];

        if (isset($ranks[$currentStatus], $ranks[$newStatus])) {
            return $ranks[$newStatus] >= $ranks[$currentStatus];
        }

        return true;
    }
}
