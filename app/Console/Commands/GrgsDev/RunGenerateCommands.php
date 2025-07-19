<?php

namespace App\Console\Commands\GrgsDev;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\File;
use function Laravel\Prompts\multiselect;

class RunGenerateCommands extends Command
{
    // O nome e a assinatura do comando.
    protected $signature = 'grgsdev:run-generate {model}';

    // A descrição do comando.
    protected $description = 'Run multiple grgsdev commands based on user input using checkboxes';

    /**
     * Execute o comando.
     */
    public function handle()
    {
        $model = $this->argument('model'); // Obtém o nome do arquivo JSON (sem a extensão)
        $jsonPath = base_path("documentation/models/json/{$model}.json");

        if (!File::exists($jsonPath)) {
            $this->error("JSON file for model {$model} not found at path {$jsonPath}.");
            return;
        }

        $jsonContent = json_decode(File::get($jsonPath), true);

        if (!$jsonContent) {
            $this->error('Invalid JSON content.');
            return;
        }
        // Array de comandos que você deseja executar
        $commands = [
            'grgsdev:migration' => "Run migration",
            'grgsdev:model' => "Generate model",
            'grgsdev:formrequest' => "Generate form request",
            'grgsdev:resource' => "Generate resource",
            'grgsdev:controller' => "Generate controller",
        ];

        // Definimos aqui os comandos que estarão selecionados por padrão
        $defaultSelected = [
            'grgsdev:model',
            'grgsdev:formrequest',
            'grgsdev:resource',
            'grgsdev:controller'
        ];

        $selectedCommands = multiselect(
            label: 'What command should be assigned?',
            options: $commands,
            default: $defaultSelected
        );


        // Executa apenas os comandos selecionados
        foreach ($selectedCommands as $commandKey) {
            $this->call($commandKey, ['model' => $model]);
            $this->info("Command {$commandKey} executed successfully.");
        }

        $this->info("All selected commands have been processed.");
    }
}
