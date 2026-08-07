<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_checkouts', function (Blueprint $table) {
            $table->text('eula_text')->nullable()->after('checkout_notes');
            $table->text('signature_path')->nullable()->after('eula_text');
            $table->timestamp('accepted_at')->nullable()->after('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('asset_checkouts', function (Blueprint $table) {
            $table->dropColumn(['eula_text', 'signature_path', 'accepted_at']);
        });
    }
};
