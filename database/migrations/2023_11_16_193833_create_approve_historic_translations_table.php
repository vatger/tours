<?php

declare(strict_types=1);

use App\Models\ApproveHistoric;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approve_historic_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(ApproveHistoric::class, 'item_id')
                ->constrained('approve_historics')
                ->cascadeOnDelete();

            $table->string('locale');

            $table->string('motive')->nullable();

            $table->unique(['item_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approve_historic_translations');
    }
};
