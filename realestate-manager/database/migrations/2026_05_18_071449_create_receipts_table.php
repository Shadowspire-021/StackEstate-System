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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 30)->unique();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('property_id');
            $table->bigInteger('total_amount_this_receipt');
            $table->bigInteger('total_received_to_date');
            $table->bigInteger('remaining_balance');
            $table->date('receipt_date');
            $table->string('docx_filename', 200);
            $table->string('google_drive_file_id', 100)->nullable();
            $table->text('google_drive_file_url')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
