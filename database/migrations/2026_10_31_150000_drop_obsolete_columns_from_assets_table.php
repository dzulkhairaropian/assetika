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
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['product_number']);
            $table->dropIndex(['invoice_number']);

            $table->dropColumn([
                'product_number',
                'mac_address_wireless',
                'mac_address_ethernet',
                'invoice_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('product_number')->nullable()->index();
            $table->string('mac_address_wireless')->nullable();
            $table->string('mac_address_ethernet')->nullable();
            $table->string('invoice_number')->nullable()->index();
        });
    }
};
