<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bistro_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 16)->unique();
            $table->string('status', 24)->default('pending');
            $table->string('fulfillment_method', 16);
            $table->string('customer_name', 120);
            $table->string('customer_phone', 32);
            $table->string('customer_email')->nullable();
            $table->string('neighborhood', 120)->nullable();
            $table->string('delivery_address', 255)->nullable();
            $table->unsignedBigInteger('subtotal_cop');
            $table->unsignedBigInteger('total_cop');
            $table->timestampsTz();
            $table->index(['bistro_id', 'status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name', 120);
            $table->unsignedBigInteger('unit_price_cop');
            $table->unsignedSmallInteger('quantity');
            $table->string('note', 300)->nullable();
            $table->unsignedBigInteger('line_total_cop');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
