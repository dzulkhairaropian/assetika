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
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            
            $table->foreignId('from_company_id')->constrained('companies');
            $table->foreignId('from_department_id')->nullable()->constrained('departments');
            $table->foreignId('from_location_id')->nullable()->constrained('locations');
            
            $table->foreignId('to_company_id')->constrained('companies');
            $table->foreignId('to_department_id')->nullable()->constrained('departments');
            $table->foreignId('to_location_id')->nullable()->constrained('locations');
            
            $table->foreignId('requested_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            
            $table->string('status')->default('pending'); // pending, approved, rejected, completed, cancelled
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_transfers');
    }
};
