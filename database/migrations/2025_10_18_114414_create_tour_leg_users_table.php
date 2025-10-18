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
        Schema::create('tour_leg_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreignId('tour_leg_id')
                ->references('id')->on('tour_legs')
                ->onDelete('cascade');
            $table->timestamp('completed_at')->nullable()->default(null);
            $table->unsignedBigInteger('fight_data_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_leg_users');
    }
};
