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
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('vendor_type', ['default', 'custom'])->default('default')->after('status');
            $table->string('vendor_name', 150)->nullable()->after('vendor_type');
            $table->string('vendor_cnic', 20)->nullable()->after('vendor_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['vendor_type', 'vendor_name', 'vendor_cnic']);
        });
    }
};
