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
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->nullOnDelete();
            $table->foreignId('status_id')->nullable()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('low_threshold')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumables');
    }
};
