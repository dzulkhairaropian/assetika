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
        Schema::create('ga_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('ga_asset_categories');
            $table->string('name');
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->string('status')->default('Available');
            $table->foreignId('current_holder_id')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ga_assets');
    }
};
