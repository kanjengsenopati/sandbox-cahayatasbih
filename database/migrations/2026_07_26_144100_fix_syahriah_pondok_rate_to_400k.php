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
        // Temukan tarif SYAHRIAH PONDOK
        $rates = DB::table('payment_rates')
            ->join('bill_types', 'payment_rates.bill_type_id', '=', 'bill_types.id')
            ->where('bill_types.name', 'not like', '%SMP%')
            ->where('bill_types.name', 'not like', '%MA%')
            ->where('bill_types.name', 'like', '%SYAHRIAH%')
            ->select('payment_rates.id')
            ->get();
            
        $rateIds = $rates->pluck('id')->toArray();
        
        if (!empty($rateIds)) {
            // Update nominal payment_rate_items
            $items = DB::table('payment_rate_items')
                ->whereIn('payment_rate_id', $rateIds)
                ->where('amount', 500000)
                ->get();
                
            $rateItemIds = $items->pluck('id')->toArray();
            
            if (!empty($rateItemIds)) {
                DB::table('payment_rate_items')
                    ->whereIn('id', $rateItemIds)
                    ->update(['amount' => 400000]);
                    
                // Update tagihan yang masih UNPAID
                DB::table('bills')
                    ->whereIn('payment_rate_item_id', $rateItemIds)
                    ->where('status', 'UNPAID')
                    ->where('amount', 500000)
                    ->update([
                        'amount' => 400000,
                        'remaining_amount' => 400000
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
