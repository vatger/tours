<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CommandLog;
use App\Traits\LogsScheduledCommands;
use Carbon\Carbon;

class CleanCommandLogs extends Command
{
    use LogsScheduledCommands;

    protected $signature = 'grgsdev:logs-command-clean';
    protected $description = 'Clean up old command logs';

    public function handle()
    {
        // Obtém o número de dias da configuração

        if (!config('app.keep_command_logs_for')) {
            $days = 7;
        } else {
            $days = config('app.keep_command_logs_for', 7);
        }

        $thresholdDate = Carbon::now()->subDays($days);

        try {
            $deletedCount = CommandLog::where('executed_at', '<', $thresholdDate)->delete();
            $outputMessage = "{$deletedCount} registros antigos de logs foram excluídos. Registros mais antigos que {$days} dias foram excluídos.";

            $this->logCommandExecution($outputMessage, true);
            $this->info($outputMessage);
        } catch (\Exception $e) {
            // Registro de falha no log
            $this->logCommandExecution($e->getMessage(), false);
            $this->error("Falha ao limpar logs de comandos antigos: {$e->getMessage()}");
        }
    }
}
