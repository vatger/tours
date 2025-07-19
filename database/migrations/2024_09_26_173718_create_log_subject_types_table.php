<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_subject_types', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();  // Ex: 'App\Models\User'
            $table->string('alias');  // Ex: 'User'
            $table->string('what_subject_name')->nullable();
            $table->longText('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_subject_types');
    }
};
