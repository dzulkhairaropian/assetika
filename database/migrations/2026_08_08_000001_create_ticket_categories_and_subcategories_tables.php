<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ticket_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('ticket_category_id')->nullable()->after('category_id')->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('ticket_subcategory_id')->nullable()->after('ticket_category_id')->constrained('ticket_subcategories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['ticket_category_id']);
            $table->dropForeign(['ticket_subcategory_id']);
            $table->dropColumn(['ticket_category_id', 'ticket_subcategory_id']);
        });

        Schema::dropIfExists('ticket_subcategories');
        Schema::dropIfExists('ticket_categories');
    }
};
