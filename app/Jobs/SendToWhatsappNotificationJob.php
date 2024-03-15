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
    protected $deviceId;
    protected $url;

    /**
     * Create a new job instance.
     */
    public function __construct($number, $message)
    {
        $this->number = $number;
        $this->message = $message;
        $this->deviceId = ApplicationSetting::latest()->value('device_id');
        $this->url = ApplicationSetting::latest()->value('link_whatsapp') . '/send';
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $client = new Client();
        try {
            $response = $client->get($this->url, [
                'query' => [
                    'device_id' => $this->deviceId,
                    'number' => $this->number,
                    'message' => $this->message,
                ],
            ]);

            $result = $response->getBody()->getContents();

            return "<pre>" . print_r($result, true);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
            return $e->getMessage();
        }
    }
}
