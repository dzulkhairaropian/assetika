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
        Schema::table('ga_assets', function (Blueprint $table) {
            $table->date('retired_at')->nullable()->after('status');
            $table->string('retirement_reason')->nullable()->after('retired_at');
            $table->text('retirement_notes')->nullable()->after('retirement_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ga_assets', function (Blueprint $table) {
            $table->dropColumn(['retired_at', 'retirement_reason', 'retirement_notes']);
        });
    }
};
