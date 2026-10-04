<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bistro_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('price_cop');
            $table->boolean('available')->default(true);
            $table->string('image_url', 2048)->nullable();
            $table->string('illustration', 32)->nullable();
            $table->string('tag')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestampsTz();
            $table->unique(['bistro_id', 'slug']);
            $table->index(['bistro_id', 'available', 'display_order']);
        });

        DB::statement('ALTER TABLE products ADD CONSTRAINT products_price_cop_nonnegative CHECK (price_cop >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
