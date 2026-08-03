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
        if (Schema::hasColumn('ga_assets', 'retirement_evidence_path')) {
            return;
        }

        Schema::table('ga_assets', function (Blueprint $table) {
            $table->string('retirement_evidence_path')->nullable()->after('retirement_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('ga_assets', 'retirement_evidence_path')) {
            return;
        }

        Schema::table('ga_assets', function (Blueprint $table) {
            $table->dropColumn('retirement_evidence_path');
        });
    }
};
