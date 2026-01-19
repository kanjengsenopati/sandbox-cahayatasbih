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
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('PENDING');
            $table->string('kta')->nullable();
            $table->string('member_branch')->nullable();
            $table->string('member_group')->nullable();
            $table->text('rejection_note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('kta');
            $table->dropColumn('member_branch');
            $table->dropColumn('member_group');
            $table->dropColumn('rejection_note');
        });
    }
};
