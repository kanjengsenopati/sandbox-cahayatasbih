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
        // Sync historical saldo_histories outlet_ids from point_of_sale_transactions
        DB::statement("
            UPDATE saldo_histories sh
            JOIN point_of_sale_transactions post ON post.saldo_history_id = sh.id
            SET sh.outlet_id = post.outlet_id
            WHERE sh.usage = 'POS' AND post.outlet_id IS NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot easily reverse data sync without backup, so do nothing
    }
};
