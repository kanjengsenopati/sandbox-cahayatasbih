<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Hapus kolom user_id sisa kegagalan migrasi sebelumnya jika ada
        if (Schema::hasColumn('officers', 'user_id')) {
            Schema::table('officers', function (Blueprint $table) {
                // Drop foreign key if it was somehow created (defensive)
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // Ignore if constraint doesn't exist
                }
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
        Schema::table('officers', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {}
            if (Schema::hasColumn('officers', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
?>
