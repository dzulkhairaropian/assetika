<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kit_asset', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('kit_license', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->foreignId('license_id')->constrained('licenses')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('kit_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->foreignId('component_id')->constrained('components')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('kit_consumable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained('kits')->cascadeOnDelete();
            $table->foreignId('consumable_id')->constrained('consumables')->cascadeOnDelete();
            $table->integer('qty')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_consumable');
        Schema::dropIfExists('kit_component');
        Schema::dropIfExists('kit_license');
        Schema::dropIfExists('kit_asset');
    }
};
