<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_prevent_negative_saldo_insert
                BEFORE INSERT ON students
                FOR EACH ROW
                WHEN NEW.saldo < 0
                BEGIN
                    SELECT RAISE(ABORT, 'Saldo santri tidak boleh bernilai negatif (Debt not allowed)');
                END;
            ");
            DB::unprepared("
                CREATE TRIGGER IF NOT EXISTS trg_prevent_negative_saldo_update
                BEFORE UPDATE OF saldo ON students
                FOR EACH ROW
                WHEN NEW.saldo < 0
                BEGIN
                    SELECT RAISE(ABORT, 'Saldo santri tidak boleh bernilai negatif (Debt not allowed)');
                END;
            ");
        } elseif ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE students ADD CONSTRAINT chk_students_saldo_non_negative CHECK (saldo >= 0)");
            } catch (\Throwable $e) {
                // Fallback if MySQL/MariaDB version handles CHECK differently
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::unprepared("DROP TRIGGER IF EXISTS trg_prevent_negative_saldo_insert");
            DB::unprepared("DROP TRIGGER IF EXISTS trg_prevent_negative_saldo_update");
        } elseif ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE students DROP CHECK chk_students_saldo_non_negative");
            } catch (\Throwable $e) {}
        }
    }
};
