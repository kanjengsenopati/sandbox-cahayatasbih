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
        // 1. Ambil semua jenis tagihan aktif
        $billTypes = DB::table('bill_types')->whereNull('deleted_at')->get();
        
        $groups = [];
        foreach ($billTypes as $bt) {
            $key = strtoupper(trim($bt->name)) . '|' . $bt->academic_year_id . '|' . $bt->bill_item_id;
            $groups[$key][] = $bt;
        }

        foreach ($groups as $key => $items) {
            if (count($items) <= 1) {
                continue;
            }

            // Pilih item pertama sebagai MASTER
            $master = $items[0];
            $duplicates = array_slice($items, 1);

            foreach ($duplicates as $dup) {
                // A. Penggabungan data tagihan (bills)
                $dupBills = DB::table('bills')->where('bill_type_id', $dup->id)->get();
                foreach ($dupBills as $dbill) {
                    // Periksa konflik kunci unik komposit
                    $conflictBill = DB::table('bills')
                        ->where('student_id', $dbill->student_id)
                        ->where('bill_type_id', $master->id)
                        ->where('academic_year_id', $dbill->academic_year_id)
                        ->where('month', $dbill->month)
                        ->where('year', $dbill->year)
                        ->where('active_status', $dbill->active_status)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($conflictBill) {
                        // Jika bentrok, transfer detail transaksi ke tagihan master
                        DB::table('transaction_details')
                            ->where('bill_id', $dbill->id)
                            ->update(['bill_id' => $conflictBill->id]);

                        // Jika tagihan duplikat sudah lunas, pastikan tagihan master juga lunas
                        if ($dbill->status === 'PAID') {
                            DB::table('bills')
                                ->where('id', $conflictBill->id)
                                ->update(['status' => 'PAID']);
                        }

                        // Hapus tagihan duplikat
                        DB::table('bills')->where('id', $dbill->id)->delete();
                    } else {
                        // Jika tidak bentrok, alihkan bill_type_id ke master
                        DB::table('bills')
                            ->where('id', $dbill->id)
                            ->update(['bill_type_id' => $master->id]);
                    }
                }

                // B. Penggabungan tarif pembayaran (payment_rates)
                $dupRates = DB::table('payment_rates')->where('bill_type_id', $dup->id)->get();
                foreach ($dupRates as $drate) {
                    $conflictRate = DB::table('payment_rates')
                        ->where('bill_type_id', $master->id)
                        ->where('type', $drate->type)
                        ->where('gender', $drate->gender)
                        ->where('jamaah_status', $drate->jamaah_status)
                        ->whereNull('deleted_at')
                        ->first();

                    if ($conflictRate) {
                        DB::table('payment_rate_classrooms')
                            ->where('payment_rate_id', $drate->id)
                            ->update(['payment_rate_id' => $conflictRate->id]);

                        DB::table('payment_rate_students')
                            ->where('payment_rate_id', $drate->id)
                            ->update(['payment_rate_id' => $conflictRate->id]);

                        DB::table('payment_rate_items')
                            ->where('payment_rate_id', $drate->id)
                            ->update(['payment_rate_id' => $conflictRate->id]);

                        DB::table('payment_rates')->where('id', $drate->id)->delete();
                    } else {
                        DB::table('payment_rates')
                            ->where('id', $drate->id)
                            ->update(['bill_type_id' => $master->id]);
                    }
                }

                // C. Penggabungan unit transfer configs
                DB::table('unit_transfer_configs')
                    ->where('bill_type_id', $dup->id)
                    ->update(['bill_type_id' => $master->id]);

                // D. Penggabungan bank mapping (bill_type_banks)
                $dupBanks = DB::table('bill_type_banks')->where('bill_type_id', $dup->id)->get();
                foreach ($dupBanks as $dbank) {
                    $conflictBank = DB::table('bill_type_banks')
                        ->where('bill_type_id', $master->id)
                        ->where('bank_id', $dbank->bank_id)
                        ->first();

                    if ($conflictBank) {
                        DB::table('bill_type_banks')->where('id', $dbank->id)->delete();
                    } else {
                        DB::table('bill_type_banks')
                            ->where('id', $dbank->id)
                            ->update(['bill_type_id' => $master->id]);
                    }
                }

                // E. Update requirements di template kartu (card_templates)
                $templates = DB::table('card_templates')->whereNotNull('exam_bill_requirements')->get();
                foreach ($templates as $t) {
                    $reqs = json_decode($t->exam_bill_requirements, true);
                    if (is_array($reqs) && in_array($dup->id, $reqs)) {
                        $reqs = array_map(function ($id) use ($dup, $master) {
                            return $id === $dup->id ? $master->id : $id;
                        }, $reqs);
                        $reqs = array_values(array_unique($reqs));

                        DB::table('card_templates')
                            ->where('id', $t->id)
                            ->update(['exam_bill_requirements' => json_encode($reqs)]);
                    }
                }

                // F. Soft delete bill type duplikat
                DB::table('bill_types')->where('id', $dup->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback tidak dimungkinkan karena data telah disatukan secara permanen
    }
};
