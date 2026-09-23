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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('order_number')->unique();
            $table->enum('order_type', ['purchase', 'rental']);
            $table->string('status');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2);
            $table->decimal('total', 15, 2);
            $table->enum('shipping_method', ['owner_delivery', 'express', 'regular', 'pickup']);
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_recipient_name');
            $table->string('shipping_phone');
            $table->text('shipping_full_address');
            $table->string('shipping_city');
            $table->string('shipping_province');
            $table->string('shipping_postal_code');
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('order_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
