<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bug Fix #2: Tambah index untuk mencegah & mendeteksi tagihan kembar.
     * Bug Fix #4: Tambah composite performance index untuk query rekap.
     *
     * Kombinasi (student_id, bill_type_id, month, year) diindex untuk:
     * - Mempercepat lookup duplikat
     * - Mempercepat query rekap per santri
     */
    public function up(): void
    {
        // Step 1: Hapus duplikat UNPAID yg paid_amount=0 sebelum buat index
        // Sisakan record terlama (id terkecil) untuk setiap kombinasi duplikat
        DB::statement("
            DELETE b1 FROM bills b1
            INNER JOIN bills b2
            ON  b1.student_id   = b2.student_id
            AND b1.bill_type_id = b2.bill_type_id
            AND b1.month        = b2.month
            AND b1.year         = b2.year
            AND b1.id           > b2.id
            WHERE b1.status      = 'UNPAID'
            AND   b1.paid_amount = 0
            AND   b1.deleted_at  IS NULL
            AND   b2.deleted_at  IS NULL
        ");

        Schema::table('bills', function (Blueprint $table) {
            // Index composite untuk lookup duplikat & sync
            $table->index(
                ['student_id', 'bill_type_id', 'month', 'year'],
                'idx_bills_student_billtype_period'
            );

            // Performance: filter status + soft-delete
            $table->index(['status', 'deleted_at'], 'idx_bills_status_deleted');

            // Performance: rekap per santri
            $table->index(
                ['student_id', 'status', 'deleted_at'],
                'idx_bills_student_status_deleted'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('idx_bills_student_billtype_period');
            $table->dropIndex('idx_bills_status_deleted');
            $table->dropIndex('idx_bills_student_status_deleted');
        });
    }
};
