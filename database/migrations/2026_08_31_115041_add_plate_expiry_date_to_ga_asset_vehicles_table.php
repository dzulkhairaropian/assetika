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
        if (!Schema::hasColumn('ga_asset_vehicles', 'plate_expiry_date')) {
            Schema::table('ga_asset_vehicles', function (Blueprint $table) {
                $table->date('plate_expiry_date')->nullable()->after('tax_due_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ga_asset_vehicles', 'plate_expiry_date')) {
            Schema::table('ga_asset_vehicles', function (Blueprint $table) {
                $table->dropColumn('plate_expiry_date');
            });
        }
    }
};
