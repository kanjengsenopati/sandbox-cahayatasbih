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
        Schema::dropIfExists('ppdb_registrations');

        Schema::create('ppdb_registrations', function (Blueprint $table) {
            // 1. Primary & Relational Keys
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('ppdb_track_id')->constrained('ppdb_tracks')->onDelete('cascade');
            $table->string('registration_code')->unique();

            // 2. Status & Admin
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'VERIFIED', 'ACCEPTED', 'REJECTED'])->default('DRAFT');
            $table->enum('payment_status', ['UNPAID', 'PAID'])->default('UNPAID');
            $table->string('payment_proof')->nullable();
            $table->text('admin_note')->nullable();

            // 3. Student Identity
            $table->string('name');
            $table->string('nisn')->nullable();
            $table->string('nik')->nullable();
            $table->string('birth_place');
            $table->date('birth_date');
            $table->enum('gender', ['L', 'P']);
            $table->string('student_phone')->nullable();
            $table->string('student_email')->nullable();

            // 4. Address
            $table->string('address_street')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();

            // 5. Parents Data
            $table->string('kk_number')->nullable();
            $table->string('father_name')->nullable();
            $table->string('father_nik')->nullable();
            $table->string('father_status')->nullable();
            $table->string('father_job')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_nik')->nullable();
            $table->string('mother_status')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('parent_phone')->nullable();

            // 6. MDTI Snapshot
            $table->boolean('is_mdti_member')->default(false);
            $table->string('mdti_branch')->nullable();
            $table->string('mdti_group')->nullable();

            // 7. Additional Data
            $table->string('origin_school')->nullable();
            $table->text('origin_school_address')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('achievements')->nullable();
            $table->string('gov_assistance')->nullable();
            $table->string('hobby')->nullable();
            $table->string('ambition')->nullable();
            $table->string('motivation')->nullable();

            // 8. Timestamps & SoftDeletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppdb_registrations');
    }
};
