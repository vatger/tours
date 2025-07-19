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
        Schema::create('user_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token_id');
            $table->string('ip_address');
            $table->string('browser');
            $table->string('browser_version');
            $table->string('platform');
            $table->string('device_type');
            $table->boolean('is_mobile');
            $table->boolean('is_tablet');
            $table->boolean('is_desktop');
            $table->timestamp('logout_at')->nullable();
            $table->integer('session_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logins');
    }
};
