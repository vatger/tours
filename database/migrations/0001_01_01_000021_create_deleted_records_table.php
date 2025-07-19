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
        Schema::create('deleted_records', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->json('subject_data'); // Dados do model excluído
            $table->timestamp('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deleted_records');
    }
};
