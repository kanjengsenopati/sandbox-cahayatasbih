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
            $table->dropColumn('password');
            $table->string('firebase_uid')->nullable();
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('goal')->nullable();
            $table->string('weight')->nullable();
            $table->string('height')->nullable();
            $table->string('pin')->nullable();
            $table->string('phone')->nullable();
            $table->string('apple_id')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('routine_exercise')->nullable();
            $table->string('duration_exercise')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable();
            $table->dropColumn('firebase_uid');
            $table->dropColumn('gender');
            $table->dropColumn('birth_date');
            $table->dropColumn('goal');
            $table->dropColumn('weight');
            $table->dropColumn('height');
            $table->dropColumn('pin');
            $table->dropColumn('phone');
            $table->dropColumn('apple_id');
            $table->dropColumn('fcm_token');
            $table->dropColumn('routine_exercise');
            $table->dropColumn('duration_exercise');
        });
    }
};
