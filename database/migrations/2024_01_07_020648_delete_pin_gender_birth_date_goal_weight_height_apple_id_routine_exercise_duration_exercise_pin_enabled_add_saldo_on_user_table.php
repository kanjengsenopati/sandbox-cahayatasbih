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
            $table->bigInteger('saldo')->default(0);
            $table->dropColumn('pin');
            $table->dropColumn('birth_date');
            $table->dropColumn('goal');
            $table->dropColumn('weight');
            $table->dropColumn('height');
            $table->dropColumn('apple_id');
            $table->dropColumn('routine_exercise');
            $table->dropColumn('duration_exercise');
            $table->dropColumn('pin_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['saldo']);
            $table->string('pin')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('goal')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('height')->nullable();
            $table->string('apple_id')->nullable();
            $table->string('routine_exercise')->nullable();
            $table->string('duration_exercise')->nullable();
            $table->boolean('pin_enabled')->default(false);
        });
    }
};
