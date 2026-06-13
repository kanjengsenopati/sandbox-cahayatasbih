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
            $table->string('nickname')->nullable()->after('name');
            $table->string('city')->nullable()->after('address');
            $table->string('province')->nullable()->after('city');
        });

        // Seed existing records with the first word of the full name
        $students = \App\Models\Student::all();
        foreach ($students as $student) {
            if ($student->name) {
                $firstName = explode(' ', trim($student->name))[0];
                $student->nickname = $firstName;
                $student->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['nickname', 'city', 'province']);
        });
    }
};
