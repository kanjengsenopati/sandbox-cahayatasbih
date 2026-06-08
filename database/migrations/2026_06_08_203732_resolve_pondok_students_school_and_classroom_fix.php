<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // PPTQ CAHAYA TASBIH school ID
    const PPTQ_ID       = 'e50046f1-9191-4c26-9d16-79f785d69b6f';
    // Active "PONDOK" classroom ID (fallback for students with deleted classrooms)
    const PONDOK_CLS_ID = '4cfd328c-2d69-4fca-96af-c23f9d431fe4';

    /**
     * Run the migrations.
     *
     * Fix 1: Set school_id = PPTQ for all students whose classroom belongs to PPTQ
     *        but whose school_id is still NULL.
     * Fix 2: For students whose current classroom is soft-deleted, reassign them
     *        to the active "PONDOK" classroom under PPTQ.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------
        // Fix 1: Assign school_id = PPTQ for any student whose classroom
        //        already belongs to PPTQ (even soft-deleted classrooms).
        // ---------------------------------------------------------------
        $pptqClassroomIds = DB::table('classrooms')
            ->where('school_id', self::PPTQ_ID)  // includes soft-deleted (no deleted_at filter)
            ->pluck('id')
            ->toArray();

        DB::table('students')
            ->whereNull('school_id')
            ->whereIn('classroom_id', $pptqClassroomIds)
            ->update(['school_id' => self::PPTQ_ID]);

        // ---------------------------------------------------------------
        // Fix 2: Reassign students who are pointing to a soft-deleted
        //        classroom to the active "PONDOK" classroom.
        // ---------------------------------------------------------------
        $deletedPptqClassroomIds = DB::table('classrooms')
            ->where('school_id', self::PPTQ_ID)
            ->whereNotNull('deleted_at')
            ->pluck('id')
            ->toArray();

        if (!empty($deletedPptqClassroomIds)) {
            DB::table('students')
                ->where('school_id', self::PPTQ_ID)
                ->whereIn('classroom_id', $deletedPptqClassroomIds)
                ->update(['classroom_id' => self::PONDOK_CLS_ID]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data-only migration — no structural changes to reverse.
    }
};

