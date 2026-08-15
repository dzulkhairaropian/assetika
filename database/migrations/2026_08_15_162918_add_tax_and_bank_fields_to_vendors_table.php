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
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('npwp_number')->nullable()->after('address');
            $table->string('npwp_name')->nullable()->after('npwp_number');
            $table->string('npwp_photo')->nullable()->after('npwp_name');
            $table->string('nib')->nullable()->after('npwp_photo');
            $table->string('bank_name')->nullable()->after('nib');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->text('notes')->nullable()->after('bank_account_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'npwp_number',
                'npwp_name',
                'npwp_photo',
                'nib',
                'bank_name',
                'bank_account_number',
                'bank_account_name',
                'notes'
            ]);
        });
    }
};
