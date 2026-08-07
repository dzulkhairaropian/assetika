<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->foreignId('maintenance_type_id')->nullable()->after('asset_id')->constrained('maintenance_types')->nullOnDelete();
        });
        Schema::table('ga_maintenance_logs', function (Blueprint $table) {
            $table->foreignId('maintenance_type_id')->nullable()->after('asset_id')->constrained('maintenance_types')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropForeign(['maintenance_type_id']);
            $table->dropColumn('maintenance_type_id');
        });
        Schema::table('ga_maintenance_logs', function (Blueprint $table) {
            $table->dropForeign(['maintenance_type_id']);
            $table->dropColumn('maintenance_type_id');
        });
    }
};
