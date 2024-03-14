<?php

namespace App\Jobs;

use App\Models\Notification;
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
        try {
            $messaging = app('firebase.messaging');
            $this->payload ? $type = get_class($this->payload) : $type = null;
            $this->payload ? $this->payload['notification_type'] =  str_replace('App\\Models\\', '', $type) : null;
            $data = [
                'title' => $this->title,
                'body'  => $this->body,
                'payload'  => $this->payload,
                'type'      => str_replace('App\\Models\\', '', $type),
                'reference_id'  => $this->payload?->id,
                'user_id' => $this->user->id
            ];
            Notification::create($data);
            if ($this->user->fcm_token) {
                $message = CloudMessage::withTarget('token', $this->user->fcm_token)
                    ->withNotification($data)
                    ->withData($data);
                $messaging->send($message);
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
