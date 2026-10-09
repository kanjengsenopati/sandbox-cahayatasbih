<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            if (Schema::hasTable('student_card_reports')) {
                DB::statement("ALTER TABLE `student_card_reports` MODIFY `reported_by` VARCHAR(36) NULL");
                DB::statement("ALTER TABLE `student_card_reports` MODIFY `processed_by` VARCHAR(36) NULL");
            }
            if (Schema::hasTable('student_barcode_histories')) {
                DB::statement("ALTER TABLE `student_barcode_histories` MODIFY `admin_id` VARCHAR(36) NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'])) {
            if (Schema::hasTable('student_card_reports')) {
                DB::statement("ALTER TABLE `student_card_reports` MODIFY `reported_by` BIGINT UNSIGNED NULL");
                DB::statement("ALTER TABLE `student_card_reports` MODIFY `processed_by` BIGINT UNSIGNED NULL");
            }
            if (Schema::hasTable('student_barcode_histories')) {
                DB::statement("ALTER TABLE `student_barcode_histories` MODIFY `admin_id` BIGINT UNSIGNED NULL");
            }
        }
    }
};
