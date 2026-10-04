<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['product_id', 'product_name', 'unit_price_cop', 'quantity', 'note', 'line_total_cop'];

    protected function casts(): array
    {
        return ['unit_price_cop' => 'integer', 'quantity' => 'integer', 'line_total_cop' => 'integer'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
