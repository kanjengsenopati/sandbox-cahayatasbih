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
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendBillWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $students;
    protected $billTypes;
    protected $deviceId;
    protected $url;


    /**
     * Create a new job instance.
     */
    public function __construct($students, $billTypes)
    {
        $this->students = $students;
        $this->billTypes = $billTypes;
        $this->deviceId = ApplicationSetting::latest()->value('device_id');
        $this->url = ApplicationSetting::latest()->value('link_whatsapp') . 'send';
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            foreach ($this->students as $student_id) {
                $student = Student::find($student_id);
                $message = SendNotifWaService::sendMessageUnpaidNotification($student, $this->billTypes);
                $this->sendToWhatsapp($student->user?->phone, $message);
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
