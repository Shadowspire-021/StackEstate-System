<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['client_id', 'payment_date'], 'idx_payments_client_date');
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->index(['client_id', 'status', 'due_date'], 'idx_installments_client_status_due');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->index('cnic', 'idx_clients_cnic');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_client_date');
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->dropIndex('idx_installments_client_status_due');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex('idx_clients_cnic');
        });
    }
};
