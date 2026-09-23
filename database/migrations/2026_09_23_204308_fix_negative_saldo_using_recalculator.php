<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Services\SaldoRecalculatorService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan rekalkulasi massal untuk memulihkan saldo negatif ke nol (zero clamping)
        // dan memperbaiki chain breaks pada history transaksi.
        // Opsi force dipakai untuk mengeksekusi di background CI/CD tanpa dry-run.
        try {
            Artisan::call('saldo:recalculate-all');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to run saldo recalculation in migration: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rekalkulasi tidak bisa di-reverse secara sepele
    }
};
