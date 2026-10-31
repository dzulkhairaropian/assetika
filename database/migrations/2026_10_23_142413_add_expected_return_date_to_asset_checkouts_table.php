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
            $table->date('expected_return_date')->nullable()->after('checked_out_at');
            $table->index(['expected_return_date', 'checked_in_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_checkouts', function (Blueprint $table) {
            $table->dropIndex(['expected_return_date', 'checked_in_at']);
            $table->dropColumn('expected_return_date');
        });
    }
};
