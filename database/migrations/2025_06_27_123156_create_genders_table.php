<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Gender;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @version V1
     * @return void
     */
    public function up()
    {
        Schema::create('genders', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255)->nullable()->unique();
            $table->string('name_translated', 255)->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_file_name')->nullable();
            $table->string('icon_file_extension')->nullable();
            $table->integer('icon_file_size')->nullable();
            $table->string('original_locale')->nullable();
            
            $table->boolean('is_active')->nullable()->default(0);
            
            
            $table->boolean('deleted_by_parent')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('gender_translations', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Gender::class, 'item_id')
                ->constrained('genders')
                ->cascadeOnDelete();

            $table->string('locale');

            $table->string('name_translated', 255)->nullable();
            
            $table->unique(['item_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gender_translations');
        Schema::dropIfExists('genders');
    }
};