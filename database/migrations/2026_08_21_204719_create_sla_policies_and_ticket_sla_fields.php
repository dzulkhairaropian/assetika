<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('priority_id')->constrained('priorities')->cascadeOnDelete();
            $table->integer('response_target_minutes')->default(60);
            $table->integer('resolution_target_hours')->default(24);
            $table->timestamps();
        });

        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->boolean('is_paused')->default(false)->after('is_closed');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->timestamp('sla_due_at')->nullable()->after('completed_time');
            $table->timestamp('sla_paused_at')->nullable()->after('sla_due_at');
            $table->integer('total_paused_minutes')->default(0)->after('sla_paused_at');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_due_at', 'sla_paused_at', 'total_paused_minutes']);
        });

        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->dropColumn('is_paused');
        });

        Schema::dropIfExists('sla_policies');
    }
};
