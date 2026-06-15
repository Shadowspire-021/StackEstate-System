<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('group', 50)->default('company')->after('key');
        });

        // Migrate existing settings to proper groups
        DB::table('settings')->where('key', 'company_name')->update(['group' => 'company']);
        DB::table('settings')->where('key', 'company_address')->update(['group' => 'company']);
        DB::table('settings')->where('key', 'vendor_name')->update(['group' => 'company']);
        DB::table('settings')->where('key', 'vendor_cnic')->update(['group' => 'company']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
