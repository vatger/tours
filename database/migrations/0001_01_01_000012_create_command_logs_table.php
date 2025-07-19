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
        Schema::create('command_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command'); // Nome do comando
            $table->text('output')->nullable(); // Saída do comando
            $table->boolean('status')->default(false); // Status de execução: sucesso ou falha
            $table->timestamp('executed_at')->useCurrent(); // Data de execução
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('command_logs');
    }
};
