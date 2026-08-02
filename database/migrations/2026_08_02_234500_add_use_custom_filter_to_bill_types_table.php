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
        if (Schema::hasTable('bill_types') && !Schema::hasColumn('bill_types', 'use_custom_filter')) {
            Schema::table('bill_types', function (Blueprint $table) {
                $table->boolean('use_custom_filter')->default(false)->after('use_gender_filter');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('bill_types') && Schema::hasColumn('bill_types', 'use_custom_filter')) {
            Schema::table('bill_types', function (Blueprint $table) {
                $table->dropColumn('use_custom_filter');
            });
        }
    }
};
