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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->enum('property_type', ['Residential Plot', 'Commercial Plot', 'House', 'Flat', 'Shop']);
            $table->string('plot_number', 50);
            $table->string('block_name', 50);
            $table->string('location', 100);
            $table->decimal('size_sqyards', 10, 2);
            $table->bigInteger('total_deal_value');
            $table->date('agreement_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
