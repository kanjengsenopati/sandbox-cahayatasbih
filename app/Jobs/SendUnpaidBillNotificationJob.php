<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use App\Models\ApplicationSetting;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use App\Services\SendNotifWaService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendUnpaidBillNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $students;
    protected $deviceId;
    protected $url;


    /**
     * Create a new job instance.
     */
    public function __construct($students)
    {
        $this->students = $students;
        $this->deviceId = ApplicationSetting::latest()->value('device_id');
        $this->url = ApplicationSetting::latest()->value('link_whatsapp') . 'send';
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            foreach ($this->students as $student) {
                $message = SendNotifWaService::sendAllBillInvoice($student);
                dispatch(new SendToWhatsappNotificationJob($student->user->phone, $message));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e);
            return $e->getMessage();
        }
    }

    /**
     * The job failed to process.
     */

    //  send to whatsapp
    public function sendToWhatsapp($number, $message)
    {
        $client = new Client();
        try {
            $response = $client->get($this->url, [
                'query' => [
                    'device_id' => $this->deviceId,
                    'number' => $number,
                    'message' => $message,
                ],
            ]);

            $result = $response->getBody()->getContents();

            return "<pre>" . print_r($result, true);
        } catch (\Exception $e) {
            Log::error('Failed to send WhatsApp notification: ' . $e);
            return $e->getMessage();
        }
    }
}
