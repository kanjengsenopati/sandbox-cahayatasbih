<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\SchedulerNotification;
use App\Jobs\SendToPushNotificationJob;
use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\Student;

class CheckBillClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-bill-class';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch the bill types that are monthly
        $billTypes = BillType::where('type', BillType::TYPE_MONTHLY)->get();

        foreach ($billTypes as $billType) {
            // Fetch the payment rates along with the classrooms in one query using eager loading
            $paymentRates = PaymentRate::with('paymentRateClassrooms')->where('bill_type_id', $billType->id)->get();

            foreach ($paymentRates as $paymentRate) {
                // Flatten the classroom IDs
                $classrooms = $paymentRate->paymentRateClassrooms->pluck('classroom_id', 'name')->toArray();

                // Find students who haven't received a bill yet and belong to the relevant classrooms
                $students = Student::whereDoesntHave('bills', function ($query) use ($billType) {
                    $query->where('bill_type_id', $billType->id);
                })->whereIn('classroom_id', $classrooms)->get();

                // Log details in the terminal
                $this->info('Bill Type: ' . $billType->name);
                $this->info('Classroom: ' . implode(', ', $classrooms->keys()->toArray()));
                $this->info('Students: ' . $students->pluck('name')->implode(', '));
            }
        }
    }
}
