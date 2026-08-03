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
            $table->foreignId('checked_out_by_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('supervisor_user_id')
                ->nullable()
                ->after('checked_out_by_user_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('checked_in_by_user_id')
                ->nullable()
                ->after('returned_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('checkin_notes')
                ->nullable()
                ->after('condition_on_return');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ga_asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['checked_out_by_user_id']);
            $table->dropForeign(['supervisor_user_id']);
            $table->dropForeign(['checked_in_by_user_id']);
            $table->dropColumn([
                'checked_out_by_user_id',
                'supervisor_user_id',
                'checked_in_by_user_id',
                'checkin_notes',
            ]);
        });
    }
};
