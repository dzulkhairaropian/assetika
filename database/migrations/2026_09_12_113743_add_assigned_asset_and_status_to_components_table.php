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
            $table->foreignId('assigned_asset_id')->nullable()->after('product_id')->constrained('assets')->nullOnDelete();
            $table->string('status')->default('Ready')->after('assigned_asset_id'); // Ready, Installed, Maintenance, Retired
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropForeign(['assigned_asset_id']);
            $table->dropColumn(['assigned_asset_id', 'status']);
        });
    }
};
