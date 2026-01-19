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
            $table->dropColumn('ppdb_student_id');
            $table->dropColumn('ppdb_parent_id');
            $table->string('no_reg')->nullable();
            $table->unsignedBigInteger('register_fee')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppdb_registrations', function (Blueprint $table) {
            $table->dropColumn('no_reg');
            $table->dropColumn('register_fee');
            $table->uuid('ppdb_student_id')->nullable();
            $table->uuid('ppdb_parent_id')->nullable();
        });
    }
};
