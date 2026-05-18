<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->unique()->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'access_scope')) {
                $table->enum('access_scope', ['pwa', 'backoffice', 'both'])->default('both')->after('email');
            }
        });
    }
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'access_scope')) {
                $table->dropColumn('access_scope');
            }
        });
    }
};
?>
