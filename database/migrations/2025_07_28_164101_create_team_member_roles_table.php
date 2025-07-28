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
        Schema::create('team_member_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Unique constraint to prevent duplicate role assignments
            $table->unique(['team_member_id', 'role_id']);
            
            // Indexes for performance
            $table->index(['team_member_id']);
            $table->index(['role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_member_roles');
    }
};
