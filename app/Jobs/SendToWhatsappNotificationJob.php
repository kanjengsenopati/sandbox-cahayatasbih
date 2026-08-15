<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use App\Models\ApplicationSetting;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Log;

class SendToWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $number;
    protected $message;
    protected $notificationLogId;
    protected $deviceId;
    protected $url;

    /**
     * Create a new job instance.
     */
    public function __construct($number, $message, $notificationLogId = null)
    {
        $this->number = $number;
        $this->message = $message;
        $this->notificationLogId = $notificationLogId;
        
        $appSetting = ApplicationSetting::latest()->first();
        $this->deviceId = $appSetting?->device_id;
        $this->url = $appSetting ? $appSetting->getNormalizedWhatsappUrl('send') : '';
        $this->afterCommit = true;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = new Client([
            'verify' => false,
            'timeout' => 10,
        ]);
        try {
            if (empty($this->url)) {
                throw new \Exception('WhatsApp Gateway URL not configured.');
            }

            $response = $client->get($this->url, [
                'query' => [
                    'device_id' => $this->deviceId,
                    'number' => $this->number,
                    'message' => $this->message,
                ],
            ]);

            $result = $response->getBody()->getContents();

            if ($this->notificationLogId) {
                \App\Models\StudentBillNotification::where('id', $this->notificationLogId)
                    ->update([
                        'status' => \App\Models\StudentBillNotification::STATUS_SUCCESS,
                        'sent_at' => now(),
                    ]);
            }

            return "<pre>" . print_r($result, true);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());

            if ($this->notificationLogId) {
                \App\Models\StudentBillNotification::where('id', $this->notificationLogId)
                    ->update([
                        'status' => \App\Models\StudentBillNotification::STATUS_FAILED,
                    ]);
            }

            return $e->getMessage();
        }
    }
}
