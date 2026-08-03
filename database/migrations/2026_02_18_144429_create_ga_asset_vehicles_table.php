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
        Schema::create('ga_asset_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->unique()->constrained('ga_assets')->cascadeOnDelete();
            $table->string('license_plate', 20)->nullable();
            $table->string('chassis_number', 100)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->string('bpkb_number', 100)->nullable();
            $table->string('stnk_number', 100)->nullable();
            $table->date('tax_due_date')->nullable();
            $table->date('last_service_date')->nullable();
            $table->integer('last_odometer')->nullable();
            $table->string('insurance_policy_number', 100)->nullable();
            $table->date('insurance_expiry_date')->nullable();
            $table->decimal('fuel_consumption', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ga_asset_vehicles');
    }
};
