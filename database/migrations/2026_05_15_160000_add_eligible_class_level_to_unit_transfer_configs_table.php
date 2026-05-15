<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_transfer_configs', function (Blueprint $table) {
            $table->string('eligible_class_level')->nullable()->after('from_school_id');
        });
    }

    public function down(): void
    {
        Schema::table('unit_transfer_configs', function (Blueprint $table) {
            $table->dropColumn('eligible_class_level');
        });
    }
};
