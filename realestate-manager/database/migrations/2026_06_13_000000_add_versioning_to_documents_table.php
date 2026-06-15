<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->unsignedInteger('version_number')->default(1)->after('uploaded_by');
            $table->unsignedBigInteger('parent_document_id')->nullable()->after('version_number');

            $table->foreign('parent_document_id')
                  ->references('id')
                  ->on('documents')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['parent_document_id']);
            $table->dropColumn(['version_number', 'parent_document_id']);
        });
    }
};
