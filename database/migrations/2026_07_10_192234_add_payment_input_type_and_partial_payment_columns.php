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
        Schema::table('bill_types', function (Blueprint $table) {
            $table->string('payment_input_type')->default('FIXED')->after('type');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->bigInteger('paid_amount')->default(0)->after('amount');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->bigInteger('amount')->nullable()->after('bill_id');
        });

        // Sync old paid bills data
        DB::table('bills')->where('status', 'PAID')->update([
            'paid_amount' => DB::raw('amount')
        ]);
        DB::table('bills')->where('status', '!=', 'PAID')->update([
            'paid_amount' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bill_types', function (Blueprint $table) {
            $table->dropColumn('payment_input_type');
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
