<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Outlet;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get Koperasi outlet ID
        $koperasiOutlet = Outlet::where('name', 'Koperasi')
            ->orWhere('code', 'KPR')
            ->first();
            
        $koperasiId = $koperasiOutlet ? $koperasiOutlet->id : '6bc5b484-07f9-49cc-aefa-00a8cf47e8d7';

        // 1. Assign all transactions with null or invalid outlet_id to Koperasi
        $validOutletIds = Outlet::pluck('id')->toArray();

        DB::table('point_of_sale_transactions')
            ->whereNull('outlet_id')
            ->orWhereNotIn('outlet_id', $validOutletIds)
            ->update(['outlet_id' => $koperasiId]);

        // 2. Update any saldo_histories that are linked to these transactions to sync their outlet_id as well
        DB::table('saldo_histories')
            ->join('point_of_sale_transactions', 'saldo_histories.id', '=', 'point_of_sale_transactions.saldo_history_id')
            ->where('point_of_sale_transactions.outlet_id', $koperasiId)
            ->update(['saldo_histories.outlet_id' => $koperasiId]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data migrations do not require rollback
    }
};
