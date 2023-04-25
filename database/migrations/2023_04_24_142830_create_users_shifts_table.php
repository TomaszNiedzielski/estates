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
        Schema::create('users_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('substitute_user_id')->references('user_id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->json('temp_changes')->nullable();
            $table->date('date_from');
            $table->date('date_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_shifts');
    }
};
