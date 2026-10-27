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
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('ppdb_registrations', 'family_card_file')) {
                $table->string('family_card_file')->nullable()->after('kta');
            }
            if (!Schema::hasColumn('ppdb_registrations', 'source_psb_id')) {
                $table->string('source_psb_id')->nullable()->index()->after('family_card_file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('ppdb_registrations', 'family_card_file')) {
                $cols[] = 'family_card_file';
            }
            if (Schema::hasColumn('ppdb_registrations', 'source_psb_id')) {
                $cols[] = 'source_psb_id';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
