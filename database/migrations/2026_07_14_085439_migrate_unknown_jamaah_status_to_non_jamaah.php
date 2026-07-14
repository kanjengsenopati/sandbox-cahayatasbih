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
    public function up(): void
    {
        // 1. Ubah default column value pada table users menjadi NON_JAMAAH
        Schema::table('users', function (Blueprint $table) {
            $table->string('jamaah_status')->default('NON_JAMAAH')->change();
        });

        // 2. Migrasi data lama dari UNKNOWN ke NON_JAMAAH
        DB::table('users')
            ->where('jamaah_status', 'UNKNOWN')
            ->update(['jamaah_status' => 'NON_JAMAAH']);

        // 3. Bersihkan data UNKNOWN dari settingan payment_rates
        $rates = DB::table('payment_rates')->get();
        foreach ($rates as $rate) {
            if ($rate->jamaah_status) {
                // Pecah status lama
                $statuses = explode(',', $rate->jamaah_status);
                $newStatuses = [];
                foreach ($statuses as $status) {
                    $statusTrim = trim($status);
                    if ($statusTrim === 'UNKNOWN') {
                        $newStatuses[] = 'NON_JAMAAH';
                    } else {
                        $newStatuses[] = $statusTrim;
                    }
                }
                // Hapus duplikat dan gabung kembali
                $newStatuses = array_unique($newStatuses);
                $newJamaahStatus = implode(',', $newStatuses);

                if ($newJamaahStatus !== $rate->jamaah_status) {
                    DB::table('payment_rates')
                        ->where('id', $rate->id)
                        ->update(['jamaah_status' => $newJamaahStatus]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('jamaah_status')->default('UNKNOWN')->change();
        });
    }
};
