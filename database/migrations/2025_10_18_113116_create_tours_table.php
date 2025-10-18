<?php

use Carbon\Carbon;
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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32);
            $table->text('description');
            $table->text('link');
            $table->text('img_url');
            $table->boolean('require_order')->default(false);
            $table->char('flight_rules', 1)->nullable()->default(null);
            $table->text('aircraft');
            $table->timestamp('begins_at')->default(Carbon::now());
            $table->timestamp('ends_at')->default(Carbon::now());
            $table->integer('forum_badge_id')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
