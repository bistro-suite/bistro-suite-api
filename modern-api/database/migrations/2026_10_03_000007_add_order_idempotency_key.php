<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->uuid('idempotency_key')->nullable();
            $table->unique(['bistro_id', 'idempotency_key'], 'orders_bistro_idem_unique');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique('orders_bistro_idem_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
