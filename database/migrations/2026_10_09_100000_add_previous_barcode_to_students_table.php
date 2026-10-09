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
        if (Schema::hasTable('students') && !Schema::hasColumn('students', 'previous_barcode')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('previous_barcode', 64)->nullable()->after('barcode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('students') && Schema::hasColumn('students', 'previous_barcode')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('previous_barcode');
            });
        }
    }
};
