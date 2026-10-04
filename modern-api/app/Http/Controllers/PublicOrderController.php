<?php

namespace App\Http\Controllers;

use App\Models\Bistro;
use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PublicOrderController
{
    public function store(Request $request, Bistro $bistro): JsonResponse
    {
        abort_unless($bistro->is_active, 404);

        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'customer_email' => ['nullable', 'email', 'max:254'],
            'fulfillment_method' => ['required', 'in:delivery,pickup'],
            'neighborhood' => ['required_if:fulfillment_method,delivery', 'nullable', 'string', 'max:120'],
            'delivery_address' => ['required_if:fulfillment_method,delivery', 'nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.note' => ['nullable', 'string', 'max:300'],
        ]);

        $existing = $bistro->orders()->where('idempotency_key', $data['idempotency_key'])->first();
        if ($existing !== null) {
            return $this->confirmation($existing, replayed: true);
        }

        try {
            $order = DB::transaction(function () use ($data, $bistro): Order {
                $bistro = Bistro::query()->lockForUpdate()->findOrFail($bistro->id);
                $deliveryFee = 0;
                $neighborhood = null;
                $pickupAddress = null;

                if ($data['fulfillment_method'] === 'delivery') {
                    if (! $bistro->delivery_enabled) {
                        throw ValidationException::withMessages(['fulfillment_method' => 'El bistró no recibe pedidos a domicilio por ahora.']);
                    }

                    $submittedNeighborhood = mb_strtolower(trim($data['neighborhood']));
                    $neighborhood = collect($bistro->delivery_neighborhoods ?? [])->first(
                        fn (string $allowed): bool => mb_strtolower(trim($allowed)) === $submittedNeighborhood,
                    );
                    if ($neighborhood === null) {
                        throw ValidationException::withMessages(['neighborhood' => 'Ese barrio está fuera de la cobertura configurada.']);
                    }
                    $deliveryFee = $bistro->delivery_fee_cop;
                } else {
                    if (! $bistro->pickup_enabled) {
                        throw ValidationException::withMessages(['fulfillment_method' => 'El bistró no ofrece recogida en el local por ahora.']);
                    }
                    $pickupAddress = $bistro->pickup_address;
                }

                $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
                $products = $bistro->products()->whereIn('id', $productIds)->where('available', true)
                    ->lockForUpdate()->get()->keyBy('id');

                if ($products->count() !== $productIds->count()) {
                    throw ValidationException::withMessages(['items' => 'Uno o más productos ya no están disponibles. Actualiza la carta e inténtalo de nuevo.']);
                }

                $lines = [];
                $subtotal = 0;
                foreach ($data['items'] as $line) {
                    $product = $products->get((int) $line['product_id']);
                    $quantity = (int) $line['quantity'];
                    $lineTotal = $product->price_cop * $quantity;
                    $subtotal += $lineTotal;
                    $lines[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'unit_price_cop' => $product->price_cop,
                        'quantity' => $quantity,
                        'note' => $line['note'] ?? null,
                        'line_total_cop' => $lineTotal,
                    ];
                }

                do {
                    $reference = 'BS-'.Str::upper(Str::random(8));
                } while (Order::where('reference', $reference)->exists());

                $order = $bistro->orders()->create([
                    'reference' => $reference,
                    'idempotency_key' => $data['idempotency_key'],
                    'status' => Order::STATUS_PENDING,
                    'fulfillment_method' => $data['fulfillment_method'],
                    'customer_name' => trim($data['customer_name']),
                    'customer_phone' => trim($data['customer_phone']),
                    'customer_email' => $data['customer_email'] ?? null,
                    'neighborhood' => $neighborhood,
                    'delivery_address' => $data['fulfillment_method'] === 'delivery' ? trim($data['delivery_address']) : null,
                    'pickup_address' => $pickupAddress,
                    'subtotal_cop' => $subtotal,
                    'delivery_fee_cop' => $deliveryFee,
                    'total_cop' => $subtotal + $deliveryFee,
                ]);
                $order->items()->createMany($lines);

                return $order;
            });
        } catch (QueryException $exception) {
            $isIdempotencyConflict = ($exception->errorInfo[0] ?? null) === '23505'
                && str_contains($exception->getMessage(), 'orders_bistro_idem_unique');
            if (! $isIdempotencyConflict) {
                throw $exception;
            }

            $order = $bistro->orders()->where('idempotency_key', $data['idempotency_key'])->firstOrFail();

            return $this->confirmation($order, replayed: true);
        }

        return $this->confirmation($order, replayed: false);
    }

    private function confirmation(Order $order, bool $replayed): JsonResponse
    {
        return response()->json(['data' => [
            'reference' => $order->reference,
            'status' => $order->status,
            'fulfillment_method' => $order->fulfillment_method,
            'subtotal_cop' => $order->subtotal_cop,
            'delivery_fee_cop' => $order->delivery_fee_cop,
            'total_cop' => $order->total_cop,
            'created_at' => $order->created_at,
        ], 'meta' => ['replayed' => $replayed]], $replayed ? 200 : 201);
    }
}
