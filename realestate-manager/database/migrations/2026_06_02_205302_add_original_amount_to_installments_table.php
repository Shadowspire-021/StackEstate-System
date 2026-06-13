<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            // Immutable original plan amount — never modified after creation
            $table->decimal('original_amount', 15, 2)->nullable()->after('amount');
        });

        // Back-fill existing records: original_amount = current amount
        // (for records already zeroed out, they'll need to be restructured)
        \DB::table('installments')->whereNull('original_amount')->update([
            'original_amount' => \DB::raw('amount')
        ]);

        // For paid installments that have amount=0, we can't recover original,
        // so we mark original_amount as NULL to indicate "unknown legacy record"
    }

    public function down(): void
    {
        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn('original_amount');
        });
    }
};
