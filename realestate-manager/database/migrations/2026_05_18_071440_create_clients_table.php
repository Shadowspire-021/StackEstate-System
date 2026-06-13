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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_id', 20)->unique();
            $table->enum('salutation', ['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Eng.']);
            $table->string('full_name', 150);
            $table->enum('father_husband_salutation', ['S/O', 'D/O', 'W/O']);
            $table->string('father_husband_name', 150);
            $table->string('cnic', 15)->unique();
            $table->string('phone', 20);
            $table->text('residential_address');
            $table->string('google_drive_folder_id', 100)->nullable();
            $table->integer('google_sheet_row')->nullable();
            $table->enum('status', ['active', 'inactive', 'completed'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
