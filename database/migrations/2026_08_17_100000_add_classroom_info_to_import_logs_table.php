<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->text('classroom_info')->nullable()->after('school_id');
        });
    }

    public function down()
    {
        Schema::table('import_logs', function (Blueprint $table) {
            $table->dropColumn('classroom_info');
        });
    }
};
