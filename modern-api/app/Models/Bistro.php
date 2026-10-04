<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bistro extends Model
{
    protected $fillable = [
        'name', 'slug', 'is_active', 'is_demo', 'delivery_enabled', 'delivery_fee_cop',
        'delivery_neighborhoods', 'pickup_enabled', 'pickup_address',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean', 'is_demo' => 'boolean',
            'delivery_enabled' => 'boolean', 'delivery_fee_cop' => 'integer',
            'delivery_neighborhoods' => 'array', 'pickup_enabled' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
