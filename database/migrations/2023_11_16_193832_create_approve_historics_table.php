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
        Schema::create('approve_historics', function (Blueprint $table) {
            $table->id();
            $table->string('approved_status')->nullable();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->text('motive')->nullable();
            $table->timestamps();
            $table->boolean('deleted_by_parent')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approve_historics');
    }
};
