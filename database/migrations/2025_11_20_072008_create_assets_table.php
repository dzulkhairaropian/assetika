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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->nullOnDelete();
            $table->foreignId('operating_system_id')->nullable()->nullOnDelete();
            $table->foreignId('status_id')->nullable()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('asset_tag')->unique()->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->string('product_number')->nullable()->index();
            $table->string('device_name')->nullable()->index();
            $table->string('user_name')->nullable()->index();
            $table->string('license_windows')->nullable();
            $table->string('license_office')->nullable();
            $table->string('mac_address_wireless')->nullable();
            $table->string('mac_address_ethernet')->nullable();
            $table->text('specification')->nullable();
            $table->text('accessories')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('release_date')->nullable();
            $table->date('warranty_expiry_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->nullable();
            $table->string('invoice_number')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
