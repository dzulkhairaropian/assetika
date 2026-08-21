<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_reservations', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('reservable');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('approved_by_id')->nullable()->constrained('users');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('pending'); // pending, approved, rejected, checked_out, completed, cancelled
            $table->text('purpose');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_reservations');
    }
};
