<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable(); // For easy per-client querying
            $table->string('action'); // 'create', 'update', 'delete', 'restore'
            $table->string('loggable_type');
            $table->unsignedBigInteger('loggable_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
