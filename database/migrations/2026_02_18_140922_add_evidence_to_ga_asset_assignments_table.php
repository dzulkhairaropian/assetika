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
        Schema::table('ga_asset_assignments', function (Blueprint $table) {
            $table->string('checkout_evidence')->nullable()->after('notes');
            $table->string('checkin_evidence')->nullable()->after('checkout_evidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ga_asset_assignments', function (Blueprint $table) {
            $table->dropColumn(['checkout_evidence', 'checkin_evidence']);
        });
    }
};
