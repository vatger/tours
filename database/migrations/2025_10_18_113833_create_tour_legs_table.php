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
        Schema::create('tour_legs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')
                ->references('id')->on('tours')
                ->onDelete('cascade');
            $table->string('departure_icao', 4);
            $table->string('arrival_icao', 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_legs');
    }
};
