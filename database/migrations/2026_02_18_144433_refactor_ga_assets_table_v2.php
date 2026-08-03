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
        Schema::table('ga_assets', function (Blueprint $table) {
            // Modify existing columns
            if (Schema::hasColumn('ga_assets', 'asset_tag')) {
                $table->renameColumn('asset_tag', 'asset_code');
            }
            if (Schema::hasColumn('ga_assets', 'purchase_cost')) {
                $table->renameColumn('purchase_cost', 'purchase_price');
            }

            // Enforce lengths per new schema
            $table->string('asset_code', 50)->change();
            $table->string('name', 150)->change();
            $table->string('serial_number', 100)->nullable()->change();

            // Add new columns
            $table->string('inventory_number', 100)->nullable()->after('asset_code');
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('ga_asset_subcategories');
            $table->string('brand', 100)->nullable()->after('subcategory_id');
            $table->string('model', 100)->nullable()->after('brand');
            $table->year('acquisition_year')->nullable()->after('serial_number');
            $table->enum('acquisition_source', ['Pembelian', 'Hibah', 'Sewa', 'Lainnya'])->default('Pembelian')->after('acquisition_year');
            $table->string('vendor_name', 150)->nullable()->after('purchase_date');
            $table->string('invoice_number', 100)->nullable()->after('vendor_name');
            $table->decimal('additional_cost', 15, 2)->default(0)->after('purchase_price');

            $table->integer('warranty_period_month')->nullable()->after('additional_cost');
            $table->date('warranty_expiry_date')->nullable()->after('warranty_period_month');

            // Use storedAs if supported, otherwise virtualAs or normal column + observers
            // User requested GENERATED ALWAYS ... STORED
            $table->decimal('total_asset_value', 15, 2)->storedAs('purchase_price + additional_cost')->after('additional_cost');
        });
    }

    public function down(): void
    {
        Schema::table('ga_assets', function (Blueprint $table) {
            // $table->renameColumn('asset_code', 'asset_tag');
            // $table->renameColumn('purchase_price', 'purchase_cost');

            // Drop generated column first to avoid dependency errors
            if (Schema::hasColumn('ga_assets', 'total_asset_value')) {
                $table->dropColumn('total_asset_value');
            }

            // $table->dropForeign(['subcategory_id']); // Likely already dropped in partial rollback
            $table->dropColumn([
                'inventory_number',
                'subcategory_id',
                'brand',
                'model',
                'acquisition_year',
                'acquisition_source',
                'vendor_name',
                'invoice_number',
                'additional_cost',
                'warranty_period_month',
                'warranty_expiry_date'
            ]);
        });
    }
};
