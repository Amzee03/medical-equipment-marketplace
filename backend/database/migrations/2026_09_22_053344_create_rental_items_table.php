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
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('equipment_unit_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('rental_due_date');
            $table->date('actual_return_date')->nullable();
            $table->integer('late_days')->default(0);
            $table->decimal('late_fee', 15, 2)->default(0);
            $table->enum('condition_at_return', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->nullable();
            $table->enum('rental_status', ['confirmed', 'active', 'awaiting_return', 'overdue', 'returned', 'completed']);
            $table->timestamps();

            $table->index('rental_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
