<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('outlet_handovers', function (Blueprint $table) {
            $table->string('handover_type')->default('KOPERASI_TO_OUTLET')->after('id');
            $table->uuid('cashier_id')->nullable()->after('recipient_id');
            $table->decimal('system_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('discrepancy', 15, 2)->default(0)->after('system_amount');
            $table->string('status')->default('APPROVED')->after('discrepancy');
            $table->uuid('verified_by')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('verified_by');

            $table->foreign('cashier_id')->references('id')->on('admins')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outlet_handovers', function (Blueprint $table) {
            $table->dropForeign(['cashier_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'handover_type',
                'cashier_id',
                'system_amount',
                'discrepancy',
                'status',
                'verified_by',
                'verified_at',
            ]);
        });
    }
};
