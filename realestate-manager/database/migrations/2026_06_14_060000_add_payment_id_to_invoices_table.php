<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->after('installment_id')
                ->constrained()->nullOnDelete();
            $table->unique('payment_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['payment_id']);
            $table->dropForeign(['payment_id']);
            $table->dropColumn('payment_id');
        });
    }
};
