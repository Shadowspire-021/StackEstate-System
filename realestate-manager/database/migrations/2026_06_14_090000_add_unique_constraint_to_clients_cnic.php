<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE clients c1
            SET cnic = CONCAT(cnic, \'-DUP-\', c1.id)
            WHERE EXISTS (
                SELECT 1 FROM clients c2
                WHERE c2.cnic = c1.cnic
                AND c2.id < c1.id
                AND c1.cnic IS NOT NULL
            )
        ');

        Schema::table('clients', function (Blueprint $table) {
            $table->unique('cnic', 'clients_cnic_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_cnic_unique');
        });
    }
};
