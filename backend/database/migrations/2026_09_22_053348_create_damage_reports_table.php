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
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->constrained('users')->restrictOnDelete();
            $table->enum('condition', ['rusak_ringan', 'rusak_berat', 'hilang']);
            $table->text('description');
            $table->decimal('repair_cost', 15, 2)->nullable();
            $table->decimal('replacement_cost', 15, 2)->nullable();
            $table->enum('status', ['pending', 'charged', 'waived']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
