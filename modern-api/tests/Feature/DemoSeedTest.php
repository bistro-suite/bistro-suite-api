<?php

namespace Tests\Feature;

use App\Models\Bistro;
use App\Models\Order;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DemoSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_varied_orders_once_and_keeps_them_stable_on_redeploy(): void
    {
        Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);
        Artisan::call('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true]);

        $bistro = Bistro::query()->where('slug', 'demo-bistro')->sole();
        $orders = $bistro->orders()->with('items')->get();

        $this->assertSame(8, $orders->count());
        $this->assertSame(8, $orders->pluck('reference')->unique()->count());
        $this->assertSame(8, $orders->whereNotNull('idempotency_key')->count());
        $this->assertSame(['cancelled', 'confirmed', 'delivered', 'pending'], $orders->pluck('status')->unique()->sort()->values()->all());
        $this->assertGreaterThan(0, $orders->sum(fn (Order $order): int => $order->items->count()));
        $this->assertTrue($orders->contains(fn (Order $order): bool => $order->fulfillment_method === 'delivery'));
        $this->assertTrue($orders->contains(fn (Order $order): bool => $order->fulfillment_method === 'pickup'));
        $this->assertTrue($orders->max('created_at')->diffInHours(now()) < 24);
        $this->assertTrue($orders->min('created_at')->diffInDays(now()) >= 4);
    }
}
