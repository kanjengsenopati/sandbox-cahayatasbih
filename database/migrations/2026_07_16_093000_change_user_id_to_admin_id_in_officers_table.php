<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // 1. Migrate existing officer data to point to valid admin records
        if (Schema::hasTable('officers') && Schema::hasTable('users') && Schema::hasTable('admins')) {
            $officers = DB::table('officers')->get();
            foreach ($officers as $officer) {
                if (empty($officer->user_id)) {
                    continue;
                }

                $user = DB::table('users')->where('id', $officer->user_id)->first();
                if (!$user) {
                    // If the referenced user does not exist, nullify user_id to prevent constraint failure
                    DB::table('officers')->where('id', $officer->id)->update(['user_id' => null]);
                    continue;
                }

                // Find matching admin by phone or email
                $admin = null;
                if (!empty($user->phone)) {
                    $admin = DB::table('admins')->where('phone', $user->phone)->first();
                }
                if (!$admin && !empty($user->email)) {
                    $admin = DB::table('admins')->where('email', $user->email)->first();
                }

                if ($admin) {
                    // Link to existing admin
                    DB::table('officers')->where('id', $officer->id)->update(['user_id' => $admin->id]);
                } else {
                    // Create a new Admin record to preserve their credentials/access
                    $emailName = \Illuminate\Support\Str::slug($user->name, '');
                    $email = $emailName . '@cahayatasbih.com';
                    $count = DB::table('admins')->where('email', $email)->count();
                    if ($count > 0) {
                        $email = $emailName . rand(100, 999) . '@cahayatasbih.com';
                    }

                    $role = DB::table('roles')->where('name', 'like', '%PETUGAS%')->first() ?? DB::table('roles')->first();
                    $roleId = $role ? $role->id : 1;
                    $adminId = (string) \Illuminate\Support\Str::uuid();

                    DB::table('admins')->insert([
                        'id' => $adminId,
                        'name' => $user->name,
                        'email' => $email,
                        'phone' => $user->phone,
                        'password' => $user->password ?? bcrypt('12345678'),
                        'avatar' => 'assets/media/avatars/150-26.jpg',
                        'is_active' => 1,
                        'role_id' => $roleId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Link to the newly created admin
                    DB::table('officers')->where('id', $officer->id)->update(['user_id' => $adminId]);
                }
            }
        }

        // 2. Perform table alterations
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
