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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('property_id');
            $table->integer('payment_number');
            $table->bigInteger('amount');
            $table->enum('payment_method', ['CASH', 'CHEQUE', 'BANK_TRANSFER', 'ONLINE']);
            $table->string('particulars', 200);
            $table->string('bank_name', 100)->nullable();
            $table->string('cheque_number', 50)->nullable();
            $table->date('payment_date');
            $table->unsignedBigInteger('receipt_id')->nullable();
            $table->boolean('synced_to_sheet')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            // receipt_id foreign key can be added after receipts table is created or skipped to avoid circular issues
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
