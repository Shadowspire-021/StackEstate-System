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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('property_id');
            $table->integer('installment_number');
            $table->bigInteger('amount');
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('installment_id')->nullable()->after('property_id');
            $table->foreign('installment_id')->references('id')->on('installments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['installment_id']);
            $table->dropColumn('installment_id');
        });

        Schema::dropIfExists('installments');
    }
};
