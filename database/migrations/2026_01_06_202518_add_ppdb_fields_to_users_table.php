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
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('USER')->after('password');
            }
            if (!Schema::hasColumn('users', 'jamaah_verification_status')) {
                $table->string('jamaah_verification_status')->default('NONE')->after('role');
            }
            if (!Schema::hasColumn('users', 'kta_image_path')) {
                $table->string('kta_image_path')->nullable()->after('jamaah_verification_status');
            }
            if (!Schema::hasColumn('users', 'member_branch')) {
                $table->string('member_branch')->nullable()->after('kta_image_path');
            }
            if (!Schema::hasColumn('users', 'member_group')) {
                $table->string('member_group')->nullable()->after('member_branch');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToRemove = ['role', 'jamaah_verification_status', 'kta_image_path', 'member_branch', 'member_group'];
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
