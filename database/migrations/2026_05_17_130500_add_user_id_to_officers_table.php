<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Hapus kolom user_id sisa kegagalan migrasi sebelumnya jika ada
        if (Schema::hasColumn('officers', 'user_id')) {
            // Cek langsung ke information_schema MySQL untuk memastikan foreign key benar-benar ada sebelum di-drop
            $foreignKeys = \Illuminate\Support\Facades\DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'officers' 
                  AND CONSTRAINT_NAME = 'officers_user_id_foreign'
            ");

            if (!empty($foreignKeys)) {
                Schema::table('officers', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            }

            Schema::table('officers', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        Schema::table('officers', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->after('photo');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Make user_id unique to enforce one‑to‑one relation
            $table->unique('user_id');
        });
    }
    public function down()
    {
        $foreignKeys = \Illuminate\Support\Facades\DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = 'officers' 
              AND CONSTRAINT_NAME = 'officers_user_id_foreign'
        ");

        if (!empty($foreignKeys)) {
            Schema::table('officers', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if (Schema::hasColumn('officers', 'user_id')) {
            Schema::table('officers', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
?>
