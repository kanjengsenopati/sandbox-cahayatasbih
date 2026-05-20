<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AuditService;

class AuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all audit scripts and log results to storage/logs/audit_*.log';

    public function handle(): int
    {
        $service = new AuditService();
        $results = $service->runAll();
        $timestamp = now()->format('Ymd_His');
        $logPath = storage_path("logs/audit_{$timestamp}.log");
        $log = "Audit run at {$timestamp}\n";
        foreach ($results as $script => $data) {
            $log .= "=== {$script} (status {$data['status']}) ===\n";
            $log .= $data['output'] . "\n\n";
        }
        \file_put_contents($logPath, $log);
        $this->info("Audit completed. Log saved to {$logPath}");
        return 0;
    }
}
