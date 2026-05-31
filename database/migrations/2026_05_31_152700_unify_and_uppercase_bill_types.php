<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Unifikasi ZARKASI
        $zarkasiMappings = [
            'ZARKASI PONDOK' => 'ZARKASI',
            'ZARKASI SMP' => 'ZARKASI',
            'Zarkasi' => 'ZARKASI',
            'ZARKASI MA' => 'ZARKASI',
        ];

        foreach ($zarkasiMappings as $old => $new) {
            DB::table('bill_types')
                ->where('name', $old)
                ->update(['name' => $new]);
        }

        // 2. Safe Delete 'NEW LKS' dan 'NEW SPP' (Hapus jika tidak ada relasi aktif)
        $targetDeletes = ['NEW LKS', 'NEW SPP'];
        foreach ($targetDeletes as $name) {
            $billType = DB::table('bill_types')->where('name', $name)->first();
            if ($billType) {
                $hasBills = DB::table('bills')->where('bill_type_id', $billType->id)->exists();
                $hasRates = DB::table('payment_rates')->where('bill_type_id', $billType->id)->exists();
                
                if (!$hasBills && !$hasRates) {
                    // Hapus di tabel anak terlebih dahulu untuk menjaga constraint
                    DB::table('bill_type_banks')->where('bill_type_id', $billType->id)->delete();
                    DB::table('bill_types')->where('id', $billType->id)->delete();
                }
            }
        }

        // 3. Ubah semua Nama Pembayaran yang tersisa menjadi UPPER CASE
        $billTypes = DB::table('bill_types')->get();
        foreach ($billTypes as $bt) {
            $upperName = Str::upper($bt->name);
            if ($bt->name !== $upperName) {
                DB::table('bill_types')
                    ->where('id', $bt->id)
                    ->update(['name' => $upperName]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback
    }
};
