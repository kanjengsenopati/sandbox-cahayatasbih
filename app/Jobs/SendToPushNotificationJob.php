<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendToPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $title;
    protected $body;
    protected $user;
    protected $payload = null;
    protected $image = null;
    /**
     * Create a new job instance.
     */
    public function __construct($title, $body, $user, $payload = null, $image = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->user = $user;
        $this->payload = $payload;
        $this->image = $image;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        NotificationService::sendTo($this->title, $this->body, $this->user, $this->payload, $this->image);
    }
}
