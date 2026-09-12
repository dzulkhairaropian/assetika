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
        Schema::table('components', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')->constrained('products')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->after('location_id')->constrained('vendors')->nullOnDelete();
            $table->date('purchase_date')->nullable()->after('purchase_cost');
            $table->integer('warranty_months')->nullable()->after('purchase_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['product_id', 'vendor_id', 'purchase_date', 'warranty_months']);
        });
    }
};
