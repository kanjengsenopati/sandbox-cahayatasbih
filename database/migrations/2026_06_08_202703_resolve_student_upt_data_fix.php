<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $students = Illuminate\Support\Facades\DB::table('students')
            ->whereNull('school_id')
            ->orWhere('school_id', '')
            ->get();

        foreach ($students as $student) {
            if (!$student->classroom_id) {
                continue;
            }

            $classroom = Illuminate\Support\Facades\DB::table('classrooms')
                ->where('id', $student->classroom_id)
                ->first();

            if (!$classroom || !$classroom->name) {
                continue;
            }

            $className = $classroom->name;
            $isSmp = false;
            $isMa = false;

            // Extract leading digits
            if (preg_match('/^\d+/', $className, $matches)) {
                $level = (int)$matches[0];
                if ($level >= 7 && $level <= 9) {
                    $isSmp = true;
                } elseif ($level >= 10 && $level <= 12) {
                    $isMa = true;
                }
            } 
            // Check Roman numerals (word boundary)
            elseif (preg_match('/^(vii|viii|ix)\b/i', $className)) {
                $isSmp = true;
            } elseif (preg_match('/^(x|xi|xii)\b/i', $className)) {
                $isMa = true;
            }

            if ($isSmp) {
                Illuminate\Support\Facades\DB::table('students')
                    ->where('id', $student->id)
                    ->update(['school_id' => '21a216e9-3043-43f6-b765-812bd02356e8']); // SMP UNGGULAN
            } elseif ($isMa) {
                Illuminate\Support\Facades\DB::table('students')
                    ->where('id', $student->id)
                    ->update(['school_id' => '761df928-0143-491e-aa46-e75c2c162c60']); // MADRASAH ALIYAH
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data migration down is typically empty or left blank.
    }
};
