<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bistro;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bistro = $this->bistro($request);
        $orders = $bistro->orders()->with('items')->latest()->limit(50)->get();

        return response()->json(['data' => $orders->map(fn (Order $order): array => $this->payload($order))]);
    }

    public function updateStatus(Request $request, int $order): JsonResponse
    {
        $bistro = $this->bistro($request);
        $data = $request->validate(['status' => ['required', 'in:confirmed,cancelled,delivered']]);

        $record = DB::transaction(function () use ($bistro, $order, $data): Order {
            $record = $bistro->orders()->lockForUpdate()->findOrFail($order);
            abort_unless(in_array($data['status'], $record->allowedNextStatuses(), true), 409, 'Ese cambio de estado no está permitido.');
            $record->forceFill(['status' => $data['status']])->save();

            return $record->load('items');
        });

        return response()->json(['data' => $this->payload($record)]);
    }

    private function bistro(Request $request): Bistro
    {
        $user = $request->user();
        abort_unless($user?->is_active && $user->bistro?->is_active, 403);

        return $user->bistro;
    }

    private function payload(Order $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $order->status,
            'fulfillment_method' => $order->fulfillment_method,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_email' => $order->customer_email,
            'neighborhood' => $order->neighborhood,
            'delivery_address' => $order->delivery_address,
            'pickup_address' => $order->pickup_address,
            'subtotal_cop' => $order->subtotal_cop,
            'delivery_fee_cop' => $order->delivery_fee_cop,
            'total_cop' => $order->total_cop,
            'created_at' => $order->created_at,
            'items' => $order->items->map(fn ($item): array => [
                'product_name' => $item->product_name,
                'unit_price_cop' => $item->unit_price_cop,
                'quantity' => $item->quantity,
                'note' => $item->note,
                'line_total_cop' => $item->line_total_cop,
            ]),
            'allowed_next_statuses' => $order->allowedNextStatuses(),
        ];
    }
}
