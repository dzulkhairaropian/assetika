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
        Schema::table('ga_asset_categories', function (Blueprint $table) {
            $table->integer('useful_life_years')->default(5)->after('depreciation_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ga_asset_categories', function (Blueprint $table) {
            $table->dropColumn('useful_life_years');
        });
    }
};
