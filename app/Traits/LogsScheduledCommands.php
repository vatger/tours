<?php

namespace App\Traits;

use App\Models\CommandLog;

trait LogsScheduledCommands
{
    protected function logCommandExecution(string $output, bool $status)
    {
        CommandLog::create([
            'command' => $this->signature, // Assinatura do comando
            'output' => $output,
            'status' => $status,
            'executed_at' => now(),
        ]);
    }
}
