<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique();
            $table->text('description')->nullable();
            $table->text('function')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->jsonb('specifications')->nullable();
            $table->enum('condition', ['baru', 'bekas_baik', 'perlu_pemeriksaan'])->default('baru');
            $table->boolean('purchase_available')->default(false);
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->integer('stock_purchase')->nullable();
            $table->boolean('rental_available')->default(false);
            $table->decimal('rental_price_daily', 15, 2)->nullable();
            $table->decimal('rental_price_weekly', 15, 2)->nullable();
            $table->decimal('rental_price_monthly', 15, 2)->nullable();
            $table->integer('min_rental_days')->nullable();
            $table->integer('max_rental_days')->nullable();
            $table->boolean('shipping_owner_delivery')->default(false);
            $table->boolean('shipping_express')->default(false);
            $table->boolean('shipping_regular')->default(false);
            $table->boolean('shipping_pickup')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
