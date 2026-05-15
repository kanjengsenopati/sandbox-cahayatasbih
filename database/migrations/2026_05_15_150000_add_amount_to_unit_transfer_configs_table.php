<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_transfer_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->default(0)->after('bill_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('unit_transfer_configs', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
    }
};
