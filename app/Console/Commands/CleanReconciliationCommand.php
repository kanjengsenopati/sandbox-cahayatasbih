<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CleanReconciliationService;

class CleanReconciliationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:clean-reconciliation 
                            {--class= : Classroom ID or Name to reconcile}
                            {--student= : Specific Student ID or NIS to reconcile}
                            {--school= : School ID to reconcile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform clean reconciliation from Master Database (cahayatasbihdb), purging local test dummy records and aligning student saldos.';

    /**
     * Execute the console command.
     */
    public function handle(CleanReconciliationService $service)
    {
        $this->info("=================================================================");
        $this->info(" CLEAN RECONCILIATION & SYNCHRONIZATION ENGINE");
        $this->info(" Single Source of Truth: mysql_master (cahayatasbihdb)");
        $this->info("=================================================================");

        $options = [];
        if ($this->option('class')) {
            $options['class_id'] = $this->option('class');
        }
        if ($this->option('student')) {
            $options['student_id'] = $this->option('student');
        }
        if ($this->option('school')) {
            $options['school_id'] = $this->option('school');
        }

        $result = $service->execute($options, function($msg) {
            $this->line(" [INFO] " . $msg);
        });

        $this->info("\n=================================================================");
        $this->info(" SUMMARY HASIL REKONSILIASI BERSIH:");
        $this->info(" - Total Santri Diproses      : " . $result['students_processed']);
        $this->info(" - Riwayat Dummy Test Dihapus : " . $result['purged_test_histories']);
        $this->info(" - Mutasi Master Asli Disinkron: " . $result['synced_master_histories']);
        $this->info("=================================================================");

        return 0;
    }
}
