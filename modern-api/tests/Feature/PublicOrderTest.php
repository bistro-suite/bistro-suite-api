<?php

namespace Tests\Feature;

use App\Models\Bistro;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_pickup_order_persists_price_snapshot_and_idempotent_replay(): void
    {
        [$bistro, $product] = $this->catalog();
        $payload = $this->payload($product, [
            'fulfillment_method' => 'pickup',
            'neighborhood' => null,
            'delivery_address' => null,
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'note' => 'Sin cebolla']],
        ]);

        $first = $this->postJson("/api/v1/public/bistros/{$bistro->slug}/orders", $payload);

        $first->assertCreated()
            ->assertJsonPath('data.fulfillment_method', 'pickup')
            ->assertJsonPath('data.subtotal_cop', 24000)
            ->assertJsonPath('data.delivery_fee_cop', 0)
            ->assertJsonPath('data.total_cop', 24000)
            ->assertJsonPath('meta.replayed', false);

        $order = Order::with('items')->sole();
        $this->assertSame('Dirección de muestra, Cúcuta', $order->pickup_address);
        $this->assertNull($order->delivery_address);
        $this->assertSame('Pastel de prueba', $order->items->sole()->product_name);
        $this->assertSame(12000, $order->items->sole()->unit_price_cop);

        $replay = $this->postJson("/api/v1/public/bistros/{$bistro->slug}/orders", [
            ...$payload,
            'customer_name' => 'Nombre cambiado en reintento',
        ]);

        $replay->assertOk()
            ->assertJsonPath('data.reference', $first->json('data.reference'))
            ->assertJsonPath('meta.replayed', true);
        $this->assertSame(1, Order::count());
    }

    public function test_delivery_normalizes_coverage_and_calculates_server_side_fee(): void
    {
        [$bistro, $product] = $this->catalog();
        $payload = $this->payload($product, [
            'neighborhood' => '  cENTRO ',
            'delivery_address' => 'Calle 10 # 5-20',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'note' => null]],
            'subtotal_cop' => 1,
            'total_cop' => 1,
            'delivery_fee_cop' => 0,
        ]);

        $this->postJson("/api/v1/public/bistros/{$bistro->slug}/orders", $payload)
            ->assertCreated()
            ->assertJsonPath('data.subtotal_cop', 12000)
            ->assertJsonPath('data.delivery_fee_cop', 5000)
            ->assertJsonPath('data.total_cop', 17000);

        $order = Order::sole();
        $this->assertSame('Centro', $order->neighborhood);
        $this->assertSame('Calle 10 # 5-20', $order->delivery_address);
        $this->assertNull($order->pickup_address);
    }

    public function test_invalid_neighborhood_or_unavailable_product_does_not_create_order(): void
    {
        [$bistro, $product] = $this->catalog();
        $payload = $this->payload($product, ['neighborhood' => 'La Playa']);

        $this->postJson("/api/v1/public/bistros/{$bistro->slug}/orders", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('neighborhood');
        $this->assertSame(0, Order::count());

        $product->update(['available' => false]);
        $payload['idempotency_key'] = 'a38e4741-56d2-4ff9-999f-9aa2eb244cde';
        $payload['neighborhood'] = 'Centro';
        $this->postJson("/api/v1/public/bistros/{$bistro->slug}/orders", $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('items');
        $this->assertSame(0, Order::count());
    }

    public function test_admin_can_advance_order_only_through_allowed_statuses(): void
    {
        [$bistro, $product] = $this->catalog();
        $order = $bistro->orders()->create([
            'reference' => 'BS-TEST0001',
            'idempotency_key' => '7e374e0d-aec6-49e2-b02a-c7284ddbe94c',
            'status' => Order::STATUS_PENDING,
            'fulfillment_method' => 'pickup',
            'customer_name' => 'Cliente de prueba',
            'customer_phone' => '3001234567',
            'subtotal_cop' => 12000,
            'delivery_fee_cop' => 0,
            'total_cop' => 12000,
            'pickup_address' => 'Dirección de muestra, Cúcuta',
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price_cop' => $product->price_cop,
            'quantity' => 1,
            'line_total_cop' => $product->price_cop,
        ]);
        $admin = User::create([
            'bistro_id' => $bistro->id,
            'name' => 'Admin de prueba',
            'email' => 'admin@example.test',
            'password' => 'password-test',
            'is_active' => true,
        ]);

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertUnauthorized();

        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.allowed_next_statuses.0', 'delivered');

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('data.allowed_next_statuses', []);

        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertStatus(409);
    }

    public function test_read_only_demo_blocks_admin_changes_while_preserving_read_access(): void
    {
        [$bistro, $product] = $this->catalog();
        $admin = User::create([
            'bistro_id' => $bistro->id,
            'name' => 'Admin demo',
            'email' => 'demo@example.test',
            'password' => 'password-test',
            'is_active' => true,
        ]);
        $order = $bistro->orders()->create([
            'reference' => 'BS-DEMO0001',
            'idempotency_key' => 'b8c0bd7a-507e-45c6-bc7c-5df02e90f313',
            'status' => Order::STATUS_PENDING,
            'fulfillment_method' => 'pickup',
            'customer_name' => 'Cliente de prueba',
            'customer_phone' => '3001234567',
            'subtotal_cop' => 12000,
            'delivery_fee_cop' => 0,
            'total_cop' => 12000,
            'pickup_address' => 'Dirección de muestra, Cúcuta',
        ]);

        config(['app.admin_demo_read_only' => true]);
        $this->actingAs($admin, 'sanctum');

        $this->getJson('/api/v1/admin/products')->assertOk();
        $this->postJson('/api/v1/admin/products', [
            'name' => 'Nuevo plato', 'category' => 'Prueba', 'price_cop' => 10000, 'available' => true,
        ])->assertForbidden()->assertJsonPath('code', 'DEMO_READ_ONLY');
        $this->putJson("/api/v1/admin/products/{$product->id}", [
            'name' => 'Nombre cambiado', 'category' => 'Prueba', 'price_cop' => 1, 'available' => false,
        ])->assertForbidden();
        $this->deleteJson("/api/v1/admin/products/{$product->id}")->assertForbidden();
        $this->patchJson('/api/v1/admin/settings', ['delivery_fee_cop' => 0])->assertForbidden();
        $this->patchJson("/api/v1/admin/orders/{$order->id}/status", ['status' => 'confirmed'])->assertForbidden();

        $this->assertSame(1, $bistro->products()->count());
        $this->assertSame('Pastel de prueba', $product->fresh()->name);
        $this->assertSame(5000, $bistro->fresh()->delivery_fee_cop);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    /** @return array{Bistro, Product} */
    private function catalog(): array
    {
        $bistro = Bistro::create([
            'name' => 'Bistró de prueba',
            'slug' => 'test-bistro',
            'is_active' => true,
            'is_demo' => true,
            'delivery_enabled' => true,
            'delivery_fee_cop' => 5000,
            'delivery_neighborhoods' => ['Centro', 'Caobos'],
            'pickup_enabled' => true,
            'pickup_address' => 'Dirección de muestra, Cúcuta',
        ]);
        $product = $bistro->products()->create([
            'slug' => 'pastel-prueba',
            'name' => 'Pastel de prueba',
            'description' => 'Producto ficticio para pruebas.',
            'category' => 'Pruebas',
            'price_cop' => 12000,
            'available' => true,
        ]);

        return [$bistro, $product];
    }

    private function payload(Product $product, array $overrides = []): array
    {
        return array_replace([
            'idempotency_key' => '5e93e15b-94b3-4eac-86c6-c3bda5fb39a4',
            'customer_name' => 'Cliente de prueba',
            'customer_phone' => '3001234567',
            'customer_email' => null,
            'fulfillment_method' => 'delivery',
            'neighborhood' => 'Centro',
            'delivery_address' => 'Calle 10 # 5-20',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'note' => null]],
        ], $overrides);
    }
}
