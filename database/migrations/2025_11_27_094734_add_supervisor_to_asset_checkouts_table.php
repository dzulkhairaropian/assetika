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
            $table->foreignId('supervisor_user_id')->nullable()->after('checked_out_to_user_id');
            $table->foreign('supervisor_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_checkouts', function (Blueprint $table) {
            $table->dropForeign(['supervisor_user_id']);
            $table->dropColumn('supervisor_user_id');
        });
    }
};
