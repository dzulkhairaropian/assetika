<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('ga_assets') || ! Schema::hasColumn('ga_assets', 'retirement_evidence_path')) {
            return;
        }

        DB::table('ga_assets')
            ->where('retirement_evidence_path', '')
            ->update(['retirement_evidence_path' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: data normalization migration.
    }
};
