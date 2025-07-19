<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use App\Models\LogSubjectType;
use App\Traits\LogsScheduledCommands;

class SeedLogSubjectTypes extends Command
{
    use LogsScheduledCommands;

    protected $signature = 'seed:log-subject-types';
    protected $description = 'Seed LogSubjectTypes with all Eloquent models';

    public function handle()
    {
        $modelsPath = app_path('Models'); // Caminho onde suas models estão localizadas
        $models = File::allFiles($modelsPath);

        foreach ($models as $model) {
            $modelName = $model->getFilenameWithoutExtension();
            $fullClassName = "App\\Models\\$modelName";

            // Verifica se a classe realmente existe
            if (class_exists($fullClassName)) {
                // Verifica se a tabela existe
                if (Schema::hasTable((new $fullClassName)->getTable())) {
                    $whatSubjectName = 'name'; // Default

                    // Obtém os atributos fillable da classe
                    $fillableAttributes = (new $fullClassName)->getFillable();

                    // Verifica se 'title' ou 'name' estão presentes no fillable
                    if (in_array('title', $fillableAttributes)) {
                        $whatSubjectName = 'title';
                    } elseif (in_array('name', $fillableAttributes)) {
                        $whatSubjectName = 'name';
                    }

                    LogSubjectType::updateOrCreate(
                        ['type' => $fullClassName],
                        [
                            'alias' => $modelName, // Altere conforme necessário
                            'what_subject_name' => $whatSubjectName // Define o que subject_name de acordo com a verificação
                        ]
                    );
                    $this->logCommandExecution("Added: $fullClassName with what_subject_name = $whatSubjectName", true);
                    $this->info("Added: $fullClassName with what_subject_name = $whatSubjectName");
                } else {
                    $this->logCommandExecution("Table does not exist for class: $fullClassName", false);
                    $this->warn("Table does not exist for class: $fullClassName");
                }
            } else {
                $this->logCommandExecution("Table does not exist for class: $fullClassName", false);
                $this->warn("Class does not exist: $fullClassName");
            }
        }

        $this->info('LogSubjectTypes seeded successfully!');
    }
}
