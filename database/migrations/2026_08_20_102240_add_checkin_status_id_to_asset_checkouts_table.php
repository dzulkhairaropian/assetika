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
        Schema::table('asset_checkouts', function (Blueprint $table) {
            $table->foreignId('checkin_status_id')->nullable()->after('condition_report')->constrained('statuses')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_checkouts', function (Blueprint $table) {
            $table->dropForeign(['checkin_status_id']);
            $table->dropColumn('checkin_status_id');
        });
    }
};
