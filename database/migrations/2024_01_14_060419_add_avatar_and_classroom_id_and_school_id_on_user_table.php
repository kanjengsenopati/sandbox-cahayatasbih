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
        Schema::table('students', function (Blueprint $table) {
            $table->string('avatar')->nullable();
            $table->uuid('classroom_id')->nullable()->after('id');
            $table->uuid('school_id')->nullable()->after('id');
            $table->string('nisn')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'classroom_id', 'school_id', 'nisn', 'avatar']);
        });
    }
};
