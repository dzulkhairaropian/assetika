<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renewals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // domain, ssl_certificate, license, vehicle_tax, vehicle_insurance, custom
            $table->nullableMorphs('renewable');
            $table->date('issued_date')->nullable();
            $table->date('expiration_date');
            $table->integer('reminder_days_before')->default(30);
            $table->foreignId('vendor_id')->nullable()->constrained('vendors');
            $table->foreignId('responsible_user_id')->nullable()->constrained('users');
            $table->decimal('cost', 15, 2)->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->string('status')->default('active'); // active, expiring_soon, expired, renewed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewals');
    }
};
