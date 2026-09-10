<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('asset_reservations');
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('category_fieldset');
        Schema::dropIfExists('custom_field_fieldset');
        Schema::dropIfExists('fieldsets');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('eulas');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cleanup migration (irreversible drop)
    }
};
