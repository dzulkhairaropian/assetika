<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('ga_assets', 'qty')) {
            return;
        }

        Schema::table('ga_assets', function (Blueprint $table) {
            $table->unsignedInteger('qty')->default(1)->after('name');
        });

        // Ensure legacy rows always have a valid quantity.
        DB::table('ga_assets')->whereNull('qty')->update(['qty' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('ga_assets', 'qty')) {
            return;
        }

        Schema::table('ga_assets', function (Blueprint $table) {
            $table->dropColumn('qty');
        });
    }
};
