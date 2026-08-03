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
        Schema::create('ga_asset_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('ga_asset_categories')->cascadeOnDelete();
            $table->string('name', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ga_asset_subcategories');
    }
};
