<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function run(): void
    {
        // Alter enum column in MySQL
        DB::statement("ALTER TABLE student_permits MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'out', 'pending_return', 'returned', 'expired') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum
        DB::statement("ALTER TABLE student_permits MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'out', 'returned', 'expired') NOT NULL DEFAULT 'pending'");
    }
};
