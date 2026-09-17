<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class SyncPaymentRateBillsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $billTypeId;
    public $timeout = 3600; // 1 hour timeout for massive billing generation

    /**
     * Create a new job instance.
     */
    public function __construct($billTypeId)
    {
        $this->billTypeId = $billTypeId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('bills:sync-rate', [
            '--bill-type' => $this->billTypeId,
            '--force' => true,
        ]);
    }
}
