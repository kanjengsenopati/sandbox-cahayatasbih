<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\SendNotifWaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendBillWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $users;
    protected $billTypes;

    /**
     * Create a new job instance.
     */
    public function __construct($users, $billTypes)
    {
        $this->users = $users;
        $this->billTypes = $billTypes;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            foreach ($this->users as $user) {
                $message = SendNotifWaService::sendMessageUnpaidNotification($user, $this->billTypes);
                dispatch(new SendToWhatsappNotificationJob($user->phone, $message));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
}
