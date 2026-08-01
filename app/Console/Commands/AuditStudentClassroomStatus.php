<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Classroom;

class AuditStudentClassroomStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'student:audit-classroom-status {--dry-run : Run audit without writing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit and synchronize student classroom_id with resolved classroom for the active academic year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $this->info("==================================================");
        $this->info("AUDIT & SINKRONISASI STATUS KELAS SISWA" . ($isDryRun ? " (DRY RUN)" : ""));
        $this->info("==================================================");

        $activeAy = AcademicYear::where('is_active', true)->first();
        if (!$activeAy) {
            $this->error("Tahun Ajaran aktif tidak ditemukan!");
            return 1;
        }

        $this->info("Tahun Ajaran Aktif: {$activeAy->name} (ID: {$activeAy->id})");

        // Find temporary class 7G if exists
        $class7G = Classroom::where('name', '7G')->first();

        $students = Student::with(['classroom', 'classroomHistories.classroom', 'bills.classroom'])
            ->get();

        $this->info("Total Siswa Diaudit: {$students->count()}");
        $this->line("");

        $updatedCount = 0;
        $syncTo8Count = 0;
        $syncTo7GCount = 0;

        foreach ($students as $student) {
            $currentClass = $student->classroom;
            $resolvedClass = $student->getClassroomForAcademicYear($activeAy->id);

            if (!$resolvedClass) {
                continue;
            }

            $currentClassName = $currentClass?->name ?? 'TANPA KELAS';
            $resolvedClassName = $resolvedClass->name;

            // Check if student classroom_id needs update
            if ($student->classroom_id !== $resolvedClass->id) {
                $this->warn("MISS MATCH: [{$student->nis}] {$student->name}");
                $this->line("   Kelas Statis (tb_students): {$currentClassName}");
                $this->info("   Kelas Teresolusi ({$activeAy->name}): {$resolvedClassName}");

                if (!$isDryRun) {
                    $student->classroom_id = $resolvedClass->id;
                    $student->save();
                    $this->info("   -> SINKRONISASI BERHASIL!");
                }

                $updatedCount++;
                if (str_starts_with($resolvedClassName, '8')) {
                    $syncTo8Count++;
                }
            } else {
                // If student is new in active AY and in class 7 other than 7G, check if needs 7G assignment
                if ($class7G && $student->getEntryYear() == $activeAy->getStartYearSafe() && str_starts_with($currentClassName, '7') && $currentClassName !== '7G') {
                    $this->warn("SISWA BARU NON-7G: [{$student->nis}] {$student->name} (Kelas {$currentClassName})");
                    if (!$isDryRun) {
                        $student->classroom_id = $class7G->id;
                        $student->save();
                        $this->info("   -> DIALOKASIKAN KE KELAS 7G BERHASIL!");
                    }
                    $syncTo7GCount++;
                    $updatedCount++;
                }
            }
        }

        $this->line("");
        $this->info("==================================================");
        $this->info("RINGKASAN AUDIT:");
        $this->info(" - Total Siswa Ter-update     : {$updatedCount}");
        $this->info(" - Disinkronkan ke Kelas 8     : {$syncTo8Count}");
        $this->info(" - Dialokasikan ke Kelas 7G    : {$syncTo7GCount}");
        $this->info("==================================================");

        return 0;
    }
}
