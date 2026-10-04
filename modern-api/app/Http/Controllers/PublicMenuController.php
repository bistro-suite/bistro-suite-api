<?php

namespace App\Http\Controllers;

use App\Models\Bistro;
use Illuminate\Http\JsonResponse;

class PublicMenuController
{
    public function __invoke(Bistro $bistro): JsonResponse
    {
        abort_unless($bistro->is_active, 404);
        $products = $bistro->products()->where('available', true)->orderBy('display_order')->orderBy('id')
            ->get(['id', 'name', 'description', 'category', 'price_cop', 'available', 'image_url', 'illustration', 'tag'])
            ->map(fn ($product): array => [
                'id' => $product->id, 'name' => $product->name, 'category' => $product->category,
                'description' => $product->description, 'price_cop' => $product->price_cop,
                'available' => $product->available, 'image_url' => $product->image_url,
                'illustration' => $product->illustration, 'tag' => $product->tag,
            ]);

        return response()->json(['data' => $products, 'meta' => [
            'demo' => $bistro->is_demo, 'bistro_slug' => $bistro->slug,
            'notice' => $bistro->is_demo ? 'Carta ilustrativa. Los platos, agrupaciones y precios no corresponden a un negocio real.' : null,
            'currency' => 'COP',
            'fulfillment' => [
                'delivery_enabled' => $bistro->delivery_enabled,
                'delivery_fee_cop' => $bistro->delivery_fee_cop,
                'delivery_neighborhoods' => $bistro->delivery_neighborhoods ?? [],
                'pickup_enabled' => $bistro->pickup_enabled,
                'pickup_address' => $bistro->pickup_address,
                'is_demo' => $bistro->is_demo,
            ],
        ]]);
    }
}
