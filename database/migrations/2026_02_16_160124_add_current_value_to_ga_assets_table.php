<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ga_assets', function (Blueprint $table) {
            $table->decimal('current_value', 15, 2)->after('purchase_cost')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ga_assets', function (Blueprint $table) {
            $table->dropColumn('current_value');
        });
    }
};
