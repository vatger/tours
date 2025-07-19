<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsScheduledCommands;

class CleanDeletedRecordsBackup extends Command
{
    use LogsScheduledCommands;
    protected $signature = 'grgsdev:deleted-backup-clean'; // Nome do comando
    protected $description = 'Clean up old deleted records backups';

    public function handle()
    {
        // Verifica se a configuração 'keep_deleted_backup' está ativa
        if (!config('app.keep_deleted_backup')) {
            $this->logCommandExecution("Backup de registros excluídos está desativado. Nenhuma ação necessária.", true);
            $this->info('Backup de registros excluídos está desativado. Nenhuma ação necessária.');
            return;
        }

        // Obtém o número de dias configurados
        $days = config('app.keep_deleted_backup_for');
        $thresholdDate = Carbon::now()->subDays($days); // Data limite

        try {
            $deletedCount = DB::table('deleted_records')
                ->where('deleted_at', '<', $thresholdDate)
                ->delete();
            $this->logCommandExecution("{$deletedCount} Backups antigos removidos com sucesso. Registros mais antigos que {$days} dias foram excluídos.", true);
            $this->info("{$deletedCount} Backups antigos removidos com sucesso. Registros mais antigos que {$days} dias foram excluídos.");
        } catch (\Exception $e) {
            // Registro de falha no log
            $this->logCommandExecution($e->getMessage(), false);
            $this->error("Falha ao limpar backups antigos: {$e->getMessage()}");
        }


        // Realiza a limpeza de backups antigos
    }
}
