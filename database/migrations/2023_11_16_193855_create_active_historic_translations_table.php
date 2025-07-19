<?php

declare(strict_types=1);

use App\Models\ActiveHistoric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('active_historic_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ActiveHistoric::class, 'item_id')
                ->constrained('active_historics')
                ->cascadeOnDelete();
            $table->string('locale');
            $table->string('motive')->nullable();
            $table->unique(['item_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('active_historic_translations');
    }
};
