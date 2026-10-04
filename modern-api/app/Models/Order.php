<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_DELIVERED = 'delivered';

    protected $fillable = [
        'bistro_id', 'reference', 'idempotency_key', 'status', 'fulfillment_method', 'customer_name',
        'customer_phone', 'customer_email', 'neighborhood', 'delivery_address', 'pickup_address',
        'subtotal_cop', 'delivery_fee_cop', 'total_cop',
    ];

    protected function casts(): array
    {
        return ['subtotal_cop' => 'integer', 'delivery_fee_cop' => 'integer', 'total_cop' => 'integer'];
    }

    public function bistro(): BelongsTo
    {
        return $this->belongsTo(Bistro::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function allowedNextStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_DELIVERED, self::STATUS_CANCELLED],
            default => [],
        };
    }
}
