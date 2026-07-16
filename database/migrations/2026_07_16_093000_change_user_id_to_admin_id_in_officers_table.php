<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('officers', function (Blueprint $table) {
            // Drop foreign key and unique constraint safely
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'officers' 
                  AND CONSTRAINT_NAME = 'officers_user_id_foreign'
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign(['user_id']);
            }

            $uniqueKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'officers' 
                  AND CONSTRAINT_NAME = 'officers_user_id_unique'
            ");

            if (!empty($uniqueKeys)) {
                $table->dropUnique(['user_id']);
            }
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->renameColumn('user_id', 'admin_id');
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
            $table->unique('admin_id');
        });
    }

    public function down()
    {
        Schema::table('officers', function (Blueprint $table) {
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'officers' 
                  AND CONSTRAINT_NAME = 'officers_admin_id_foreign'
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign(['admin_id']);
            }

            $uniqueKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'officers' 
                  AND CONSTRAINT_NAME = 'officers_admin_id_unique'
            ");

            if (!empty($uniqueKeys)) {
                $table->dropUnique(['admin_id']);
            }
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->renameColumn('admin_id', 'user_id');
        });

        Schema::table('officers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique('user_id');
        });
    }
};
