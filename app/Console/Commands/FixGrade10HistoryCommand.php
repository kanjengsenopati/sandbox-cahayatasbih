<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\School;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\StudentClassroomHistory;
use App\Models\Bill;
use App\Models\BillType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixGrade10HistoryCommand extends Command
{
    protected $signature = 'fix:grade10-history {--dry-run : Only show what will be changed without actually making changes}';
    protected $description = 'Memperbaiki anomali student_classroom_histories dan tagihan masa lalu untuk siswa kelas 10 MA';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info("--- RUNNING IN DRY RUN MODE ---");
        } else {
            $this->warn("--- RUNNING IN EXECUTION MODE ---");
        }

        $maSchool = School::where('name', 'like', '%MADRASAH ALIYAH%')->first();
        if (!$maSchool) {
            $this->error("MADRASAH ALIYAH school not found.");
            return;
        }

        $grade10Classrooms = Classroom::where('school_id', $maSchool->id)
            ->where('name', 'like', '10%')
            ->pluck('id')->toArray();

        $ay2627 = AcademicYear::where('name', '2026/2027')->first();
        $ay2526 = AcademicYear::where('name', '2025/2026')->first();
        $ay2425 = AcademicYear::where('name', '2024/2025')->first();

        $students = Student::whereIn('classroom_id', $grade10Classrooms)->get();
        $countAffected = 0;

        foreach ($students as $student) {
            $histories = StudentClassroomHistory::where('student_id', $student->id)->get();
            $h2526 = $histories->where('academic_year_id', $ay2526->id)->first();
            $h2425 = $histories->where('academic_year_id', $ay2425->id)->first();

            $isAnomalous = false;
            if ($h2526 && in_array($h2526->classroom_id, $grade10Classrooms)) {
                $isAnomalous = true;
            }
            if ($h2425 && in_array($h2425->classroom_id, $grade10Classrooms)) {
                $isAnomalous = true;
            }

            if (!$isAnomalous) continue;
            
            $countAffected++;
            $this->info("Processing Student: {$student->name} (Enrolled: {$student->created_at->format('Y-m-d')})");

            $isAlumni = false;

            // Process 25/26
            if ($h2526 && in_array($h2526->classroom_id, $grade10Classrooms)) {
                $originalClass2526 = $this->findOriginalClassroom($student->id, $ay2526->id);
                if ($originalClass2526) {
                    $originalClassModel = Classroom::with('school')->find($originalClass2526);
                    $this->line("  [2025/2026] Found original class: " . $originalClassModel->name);
                    
                    if (str_contains(strtoupper($originalClassModel->school->name), 'SMP')) {
                        if (!$isDryRun) {
                            $h2526->update(['classroom_id' => $originalClass2526]);
                        }
                        $isAlumni = true;
                    } else {
                        // Original class is not SMP (e.g. they were enrolled early as MA students)
                        $this->line("  [2025/2026] Original class is MA. Deleting anomalous history.");
                        if (!$isDryRun) $h2526->delete();
                    }
                } else {
                    $this->line("  [2025/2026] No original bills found before Jul 2026. Deleting history.");
                    if (!$isDryRun) $h2526->delete();
                }
                $this->deleteAnomalousBills($student->id, $ay2526->id, $grade10Classrooms, $isDryRun);
            }

            // Process 24/25
            if ($h2425 && in_array($h2425->classroom_id, $grade10Classrooms)) {
                $originalClass2425 = $this->findOriginalClassroom($student->id, $ay2425->id);
                if ($originalClass2425) {
                    $originalClassModel = Classroom::with('school')->find($originalClass2425);
                    $this->line("  [2024/2025] Found original class: " . $originalClassModel->name);
                    
                    if (str_contains(strtoupper($originalClassModel->school->name), 'SMP')) {
                        if (!$isDryRun) {
                            $h2425->update(['classroom_id' => $originalClass2425]);
                        }
                        $isAlumni = true;
                    } else {
                        // Original class is not SMP
                        $this->line("  [2024/2025] Original class is MA. Deleting anomalous history.");
                        if (!$isDryRun) $h2425->delete();
                    }
                } else {
                    $this->line("  [2024/2025] No original bills found before Jul 2026. Deleting history.");
                    if (!$isDryRun) $h2425->delete();
                }
                $this->deleteAnomalousBills($student->id, $ay2425->id, $grade10Classrooms, $isDryRun);
            }

            // If proven Alumni, check 26/27 PENDAFTARAN bill
            if ($isAlumni) {
                $this->handleRegistrationBill($student->id, $ay2627->id, $isDryRun);
            }
        }

        $this->info("Done! Processed $countAffected affected students.");
    }

    private function findOriginalClassroom($studentId, $ayId)
    {
        $oldBill = DB::table('bills')
            ->join('bill_types', 'bills.bill_type_id', '=', 'bill_types.id')
            ->where('bills.student_id', $studentId)
            ->where('bill_types.academic_year_id', $ayId)
            ->where('bills.created_at', '<', '2026-07-24 00:00:00')
            ->select('bills.classroom_id')
            ->orderBy('bills.created_at', 'asc')
            ->first();
            
        return $oldBill ? $oldBill->classroom_id : null;
    }

    private function deleteAnomalousBills($studentId, $ayId, $maClassroomIds, $isDryRun)
    {
        $anomalousBills = Bill::where('student_id', $studentId)
            ->whereHas('billType', function($q) use ($ayId) {
                $q->where('academic_year_id', $ayId);
            })
            ->whereIn('classroom_id', $maClassroomIds)
            ->get();

        foreach ($anomalousBills as $bill) {
            $this->line("    -> Deleting anomalous bill: {$bill->billType->name} (Amount: {$bill->amount})");
            if (!$isDryRun) $bill->delete();
        }
    }

    private function handleRegistrationBill($studentId, $ayId, $isDryRun)
    {
        $registrationBills = Bill::where('student_id', $studentId)
            ->whereHas('billType', function($q) use ($ayId) {
                $q->where('academic_year_id', $ayId)
                  ->where('name', 'like', '%PENDAFTARAN%');
            })
            ->get();

        foreach ($registrationBills as $bill) {
            // Kita ingin menghapus jika nominalnya BUKAN tarif alumni (1.000.000)
            if ($bill->amount != 1000000) {
                if ($bill->paid_amount == 0) {
                    $this->line("  [ALUMNI FIX] Deleting incorrect UNPAID Registration bill (Amount: {$bill->amount})");
                    if (!$isDryRun) $bill->delete();
                } else {
                    $this->warn("  [WARNING] Incorrect Registration bill (Amount: {$bill->amount}) is ALREADY PARTIALLY/FULLY PAID (Paid: {$bill->paid_amount}). Needs manual handling!");
                }
            } else {
                $this->line("  [ALUMNI FIX] Registration bill is already correct (Amount: 1000000).");
            }
        }
    }
}
