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
        if (\DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            // Change payment_method column from ENUM to VARCHAR to support 'PO' and other future methods
            $table->string('payment_method', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            // Revert back to ENUM
            $table->enum('payment_method', ['CASH', 'CHEQUE', 'BANK_TRANSFER', 'ONLINE'])->change();
        });
    }
};
