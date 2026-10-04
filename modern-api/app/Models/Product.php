<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'category', 'price_cop', 'available',
        'image_url', 'illustration', 'tag', 'display_order',
    ];

    protected function casts(): array
    {
        return ['price_cop' => 'integer', 'available' => 'boolean', 'display_order' => 'integer'];
    }

    public function bistro(): BelongsTo
    {
        return $this->belongsTo(Bistro::class);
    }
}
