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
        if (!Schema::hasTable('asset_checkouts')) {
            Schema::create('asset_checkouts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_id')->constrained()->onDelete('cascade');
                $table->foreignId('checked_out_by_user_id')->constrained('users'); // Person who checked out
                $table->foreignId('checked_out_to_user_id')->constrained('users'); // Person who received asset
                $table->foreignId('department_id')->nullable()->constrained();
                $table->foreignId('location_id')->nullable()->constrained();

                // Evidence attachments
                $table->string('checkout_evidence_file_path')->nullable();
                $table->string('checkout_evidence_file_name')->nullable();

                $table->timestamp('checked_out_at')->nullable();
                $table->text('checkout_notes')->nullable();

                // Check-in fields
                $table->timestamp('checked_in_at')->nullable();
                $table->foreignId('checked_in_by_user_id')->nullable()->constrained('users');
                $table->text('condition_report')->nullable();
                $table->string('checkin_evidence_file_path')->nullable();
                $table->string('checkin_evidence_file_name')->nullable();
                $table->text('checkin_notes')->nullable();

                $table->timestamps();

                $table->index(['asset_id', 'checked_out_at']);
                $table->index(['checked_out_to_user_id', 'checked_out_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('asset_checkouts');
        Schema::enableForeignKeyConstraints();
    }
};
