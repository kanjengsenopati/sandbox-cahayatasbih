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
        if (Schema::hasTable('lapor_pak_reports') && !Schema::hasColumn('lapor_pak_reports', 'admin_note')) {
            Schema::table('lapor_pak_reports', function (Blueprint $table) {
                $table->text('admin_note')->nullable()->after('keterangan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('lapor_pak_reports') && Schema::hasColumn('lapor_pak_reports', 'admin_note')) {
            Schema::table('lapor_pak_reports', function (Blueprint $table) {
                $table->dropColumn('admin_note');
            });
        }
    }
};
