<?php

namespace App\Jobs;


use App\Models\Bill;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Services\SendNotifWaService;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentBillNotification;
use Illuminate\Queue\InteractsWithQueue;
use App\Jobs\SendToWhatsappNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendUnpaidBillNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Ambil data siswa yang memiliki tagihan belum dibayar
            $students = Student::where('status', Student::STATUS_ACTIVE)->whereHas('bills', function ($query) {
                $query->where('status', Bill::STATUS_UNPAID);
            })->latest()->get();

            // Iterasi setiap siswa dan kirim notifikasi
            foreach ($students as $index => $student) {
                // Pastikan siswa memiliki user dan nomor telepon
                if (!$student->user || !$student->user->phone) {
                    Log::warning("Siswa dengan ID {$student->id} tidak memiliki user atau nomor telepon.");
                    continue;
                }

                // Dapatkan pesan notifikasi
                $message = SendNotifWaService::sendAllBillInvoice($student);

                // Jika pesan tidak null, kirim notifikasi dan simpan log
                if ($message !== null) {
                    // Kirim notifikasi dengan delay sesuai urutan siswa
                    dispatch(new SendToWhatsappNotificationJob($student->user->phone, $message))
                        ->delay(now()->addSeconds($index));

                    // Simpan atau perbarui log notifikasi
                    StudentBillNotification::updateOrCreate(
                        ['student_id' => $student->id],
                        [
                            'message' => $message,
                            'status' => StudentBillNotification::STATUS_SUCCESS,
                            'sent_at' => now(),
                        ]
                    );
                } else {
                    Log::warning("Pesan notifikasi untuk siswa dengan ID {$student->id} kosong.");
                }
            }
        } catch (\Exception $e) {
            Log::error('Gagal mengirim WA Blast: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
