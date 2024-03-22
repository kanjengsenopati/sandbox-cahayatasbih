<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendBillWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;

    /**
     * Create a new job instance.
     */
    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
}
