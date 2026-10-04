<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bistros', function (Blueprint $table): void {
            $table->boolean('delivery_enabled')->default(false);
            $table->unsignedInteger('delivery_fee_cop')->default(0);
            $table->json('delivery_neighborhoods')->default('[]');
            $table->boolean('pickup_enabled')->default(true);
            $table->string('pickup_address', 255)->nullable();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->unsignedInteger('delivery_fee_cop')->default(0);
            $table->string('pickup_address', 255)->nullable();
        });

        DB::table('bistros')->where('slug', 'demo-bistro')->update([
            'delivery_enabled' => true,
            'delivery_fee_cop' => 5000,
            'delivery_neighborhoods' => json_encode(['Centro', 'Caobos', 'La Riviera']),
            'pickup_enabled' => true,
            'pickup_address' => 'Dirección de muestra, Cúcuta',
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['delivery_fee_cop', 'pickup_address']);
        });

        Schema::table('bistros', function (Blueprint $table): void {
            $table->dropColumn(['delivery_enabled', 'delivery_fee_cop', 'delivery_neighborhoods', 'pickup_enabled', 'pickup_address']);
        });
    }
};
