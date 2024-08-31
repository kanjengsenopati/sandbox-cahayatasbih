<?php

namespace App\Console\Commands;

use App\Models\BillType;
use App\Models\PaymentRate;
use App\Models\Student;
use App\Services\PaymentRateService;
use Illuminate\Console\Command;

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
    protected $description = 'Check and create bills for students in classes';

    /**
     * @var PaymentRateService
     */
    protected $paymentRateService;

    /**
     * Create a new command instance.
     *
     * @param PaymentRateService $paymentRateService
     */
    public function __construct(PaymentRateService $paymentRateService)
    {
        parent::__construct();
        $this->paymentRateService = $paymentRateService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch the bill types that are monthly
        $billTypes = BillType::whereHas(
            'academicYear',
            function ($query) {
                $query->where('is_active', true);
            }
        )->get();

        foreach ($billTypes as $billType) {
            // Fetch the payment rates along with the classrooms and payment items
            $paymentRates = PaymentRate::with(['paymentRateClassrooms', 'paymentRateItems'])
                ->where('bill_type_id', $billType->id)
                ->get();

            foreach ($paymentRates as $paymentRate) {
                // Skip if paymentRateItems is empty
                if ($paymentRate->paymentRateItems->isEmpty()) {
                    $this->warn("Skipping bill creation for Payment Rate ID {$paymentRate->id} due to empty paymentRateItems.");
                    continue;
                }

                // Flatten the classroom IDs
                $classrooms = $paymentRate->paymentRateClassrooms->pluck('classroom_id')->toArray();

                // Find students who haven't received a bill yet and belong to the relevant classrooms
                $students = Student::whereDoesntHave('bills', function ($query) use ($billType) {
                    $query->where('bill_type_id', $billType->id);
                })->whereIn('classroom_id', $classrooms)->get();


                foreach ($students as $student) {
                    // Prepare the data array
                    $data = [
                        'classrooms' => [$student->classroom_id],
                        'price' => [],
                        'months' => [],
                        'year' => [],
                    ];

                    // Loop through each payment rate item to create bills
                    foreach ($paymentRate->paymentRateItems as $item) {
                        $billAmount = $item->amount;
                        $billMonth = $item->month;
                        $billYear = $item->year;

                        // Populate the data array
                        $data['price'][$billMonth] = $billAmount;
                        $data['months'][] = $billMonth;
                        $data['year'][$billMonth] = $billYear;
                        $data['tahun_' . $billMonth] = $billYear;
                        $data['bulan_' . $billMonth] = $billAmount;
                    }

                    // Call the service method once for each student with the accumulated data
                    $this->paymentRateService->createBillsForStudents($paymentRate, $billType, $data);
                }


                // Log details in the terminal
                $this->info('Bill Type: ' . $billType->name);
                $this->info('Classroom IDs: ' . implode(', ', $classrooms));
                $this->info('Students: ' . $students->pluck('name')->implode(', '));
            }
        }
    }
}
