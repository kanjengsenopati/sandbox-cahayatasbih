<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to normalize classroom names.
     */
    private function normalizeClassroomName(?string $name): string
    {
        if (is_null($name)) {
            return '';
        }

        // 1. Remove all spaces and hyphens
        $cleaned = str_replace([' ', '-'], '', $name);
        
        // 2. Extract grade part and letter part.
        // Grade part can be Roman numerals (XII, XI, X, IX, VIII, VII, VI, V, IV, III, II, I) or digits.
        if (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I|12|11|10|[789])([A-Za-z]+)$/i', $cleaned, $matches)) {
            $grade = strtoupper($matches[1]);
            $letter = strtoupper($matches[2]);
            
            // Map Roman numerals to Arabic numbers
            $romanMap = [
                'XII' => '12',
                'XI' => '11',
                'X' => '10',
                'IX' => '9',
                'VIII' => '8',
                'VII' => '7',
                'VI' => '6',
                'V' => '5',
                'IV' => '4',
                'III' => '3',
                'II' => '2',
                'I' => '1',
            ];
            
            if (isset($romanMap[$grade])) {
                $grade = $romanMap[$grade];
            }
            
            return $grade . $letter;
        }
        
        return strtoupper($cleaned);
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Get all classrooms (including soft deleted)
            $classrooms = DB::table('classrooms')->get();

            // Group classrooms by school_id and normalized name
            $groups = [];
            foreach ($classrooms as $classroom) {
                $norm = $this->normalizeClassroomName($classroom->name);
                $groups[$classroom->school_id][$norm][] = $classroom;
            }

            foreach ($groups as $schoolId => $schoolGroups) {
                foreach ($schoolGroups as $normName => $classroomList) {
                    // Pick the surviving classroom:
                    // 1. Prefer the one whose name is already exactly the normalized name.
                    // 2. Otherwise, prefer the one with the oldest created_at or just the first one.
                    $surviving = null;
                    foreach ($classroomList as $c) {
                        if ($c->name === $normName) {
                            $surviving = $c;
                            break;
                        }
                    }
                    if (!$surviving) {
                        $surviving = $classroomList[0];
                        // Rename the surviving classroom to the normalized name
                        DB::table('classrooms')
                            ->where('id', $surviving->id)
                            ->update([
                                'name' => $normName,
                                'updated_at' => now()
                            ]);
                    }

                    // Duplicates are all classrooms in the group except the surviving one
                    $duplicates = array_filter($classroomList, fn($c) => $c->id !== $surviving->id);

                    foreach ($duplicates as $duplicate) {
                        $survivingId = $surviving->id;
                        $duplicateId = $duplicate->id;

                        // 1. payment_rate_classrooms deduplication
                        // Find payment_rates that are linked to BOTH surviving and duplicate classrooms
                        $survivingRates = DB::table('payment_rate_classrooms')
                            ->where('classroom_id', $survivingId)
                            ->pluck('payment_rate_id')
                            ->toArray();

                        // Delete duplicate links to avoid unique constraint violations or duplicates
                        DB::table('payment_rate_classrooms')
                            ->where('classroom_id', $duplicateId)
                            ->whereIn('payment_rate_id', $survivingRates)
                            ->delete();

                        // Update remaining duplicate links to surviving classroom
                        DB::table('payment_rate_classrooms')
                            ->where('classroom_id', $duplicateId)
                            ->update(['classroom_id' => $survivingId]);

                        // 2. student_classroom_histories deduplication
                        // Find students that have history in BOTH surviving and duplicate classrooms for the same academic year
                        $survivingHistories = DB::table('student_classroom_histories')
                            ->where('classroom_id', $survivingId)
                            ->select('student_id', 'academic_year_id')
                            ->get()
                            ->map(fn($row) => $row->student_id . '_' . $row->academic_year_id)
                            ->toArray();

                        $duplicateHistories = DB::table('student_classroom_histories')
                            ->where('classroom_id', $duplicateId)
                            ->get();

                        foreach ($duplicateHistories as $dh) {
                            $key = $dh->student_id . '_' . $dh->academic_year_id;
                            if (in_array($key, $survivingHistories)) {
                                // Delete duplicate history
                                DB::table('student_classroom_histories')->where('id', $dh->id)->delete();
                            } else {
                                // Update history to point to surviving classroom
                                DB::table('student_classroom_histories')
                                    ->where('id', $dh->id)
                                    ->update(['classroom_id' => $survivingId]);
                            }
                        }

                        // 3. Update other referencing tables
                        DB::table('students')
                            ->where('classroom_id', $duplicateId)
                            ->update(['classroom_id' => $survivingId]);

                        DB::table('bills')
                            ->where('classroom_id', $duplicateId)
                            ->update(['classroom_id' => $survivingId]);

                        DB::table('student_achievements')
                            ->where('classroom_id', $duplicateId)
                            ->update(['classroom_id' => $survivingId]);

                        DB::table('student_counseling_scores')
                            ->where('classroom_id', $duplicateId)
                            ->update(['classroom_id' => $survivingId]);

                        DB::table('study_grades')
                            ->where('classroom_id', $duplicateId)
                            ->update(['classroom_id' => $survivingId]);

                        DB::table('unit_transfer_configs')
                            ->where('to_classroom_id', $duplicateId)
                            ->update(['to_classroom_id' => $survivingId]);

                        // 4. Delete the duplicate classroom record
                        DB::table('classrooms')->where('id', $duplicateId)->delete();
                    }

                    // Rename single non-duplicate classroom if its name is not normalized yet
                    if (count($duplicates) === 0 && $surviving->name !== $normName) {
                        DB::table('classrooms')
                            ->where('id', $surviving->id)
                            ->update([
                                'name' => $normName,
                                'updated_at' => now()
                            ]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data merge migration cannot be safely reverted.
    }
};
