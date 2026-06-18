<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Hari Libur (Holidays)
        Schema::create('holidays', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('date')->unique();
            $table->string('name');
            $table->boolean('is_national')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Shift Kerja Standar
        Schema::create('working_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g. "Shift Pagi", "Shift Siang", "Full Day Guru"
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_period')->default(15); // Toleransi terlambat dalam menit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Penjadwalan Shift Bulanan Karyawan (Scheduler Shift Bulanan)
        Schema::create('employee_monthly_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type'); // App\Models\Admin atau App\Models\User
            $table->uuid('presensiable_id');
            $table->uuid('working_shift_id')->nullable(); // Null jika libur/off
            $table->date('date');
            $table->boolean('is_holiday')->default(false); // Apakah hari libur bagi karyawan tersebut
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['presensiable_type', 'presensiable_id'], 'emp_monthly_presensiable_idx');
            $table->unique(['presensiable_type', 'presensiable_id', 'date'], 'emp_monthly_date_unique');
            $table->foreign('working_shift_id')->references('id')->on('working_shifts')->onDelete('set null');
        });

        // 4. Jadwal Sholat & Batas Iqomah Masjid
        Schema::create('prayer_times', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('prayer_name')->unique(); // Subuh, Dzuhur, Ashar, Maghrib, Isya
            $table->time('adzan_time'); // Waktu adzan
            $table->time('iqomah_time'); // Waktu iqomah (mulai sholat)
            $table->integer('grace_period')->default(5); // Toleransi keterlambatan setelah iqomah (menit)
            $table->timestamps();
        });

        // 5. Mesin/Alat Biometrik Terdaftar (Fingerprint/Face Recognition)
        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_name');
            $table->string('device_ip')->nullable();
            $table->string('location'); // e.g. "Gerbang Depan", "Masjid", "Koperasi"
            $table->string('auth_token')->unique(); // Token autentikasi untuk API webhook alat
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Pemetaan PIN Biometrik ke User/Siswa
        Schema::create('biometric_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type'); // App\Models\Student, App\Models\Admin, App\Models\User
            $table->uuid('presensiable_id');
            $table->enum('biometric_type', ['fingerprint', 'face']);
            $table->integer('biometric_index')->default(0); // Indeks sidik jari (0-9) atau wajah
            $table->string('device_pin'); // ID/PIN yang terdaftar di mesin
            $table->text('template_data')->nullable(); // Embedding wajah / template fingerprint
            $table->timestamps();

            $table->index(['presensiable_type', 'presensiable_id'], 'bio_mapping_presensiable_idx');
            $table->unique(['presensiable_type', 'presensiable_id', 'biometric_type', 'biometric_index'], 'bio_mapping_unique');
            $table->unique(['device_pin', 'biometric_type'], 'bio_device_pin_unique');
        });

        // 7. Log Kehadiran Utama (Siswa/Santri & Karyawan)
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type'); // App\Models\Student, App\Models\Admin, App\Models\User
            $table->uuid('presensiable_id');
            $table->enum('activity_type', ['school', 'prayer', 'kajian', 'work']);
            $table->string('activity_name'); // e.g. "Presensi Masuk", "Subuh", "Kajian Kitab Kuning", "Jam Kerja"
            $table->uuid('schedule_id')->nullable(); // Jika kajian, hubungkan ke tabel schedules
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'permit', 'sick'])->default('present');
            $table->integer('late_minutes')->default(0); // Jumlah menit terlambat (dari iqomah / start_time + grace_period)
            $table->enum('method', ['manual', 'barcode', 'fingerprint', 'face_recognition'])->default('manual');
            $table->uuid('device_id')->nullable(); // ID Alat biometrik
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('photo_path')->nullable(); // Foto bukti kehadiran
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['presensiable_type', 'presensiable_id'], 'attendance_presensiable_idx');
            $table->foreign('device_id')->references('id')->on('biometric_devices')->onDelete('set null');
        });

        // 8. Konfigurasi Gaji Karyawan (Customizable)
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type');
            $table->uuid('presensiable_id');
            $table->decimal('base_salary', 12, 2)->default(0); // Gaji Pokok
            $table->decimal('attendance_allowance', 12, 2)->default(0); // Tunjangan Kehadiran per hari hadir
            $table->decimal('transport_allowance', 12, 2)->default(0); // Tunjangan Transportasi per hari hadir
            $table->decimal('lateness_penalty_per_minute', 12, 2)->default(0); // Denda terlambat per menit
            $table->decimal('absence_penalty', 12, 2)->default(0); // Denda mangkir per hari absen
            $table->timestamps();

            $table->unique(['presensiable_type', 'presensiable_id'], 'emp_salary_unique');
        });

        // 9. Slip Gaji Karyawan Bulanan (Drafting & Approval)
        Schema::create('salary_slips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type');
            $table->uuid('presensiable_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('total_present_days')->default(0);
            $table->integer('total_late_minutes')->default(0);
            $table->integer('total_absent_days')->default(0);
            $table->decimal('base_salary', 12, 2);
            $table->decimal('total_attendance_allowance', 12, 2)->default(0);
            $table->decimal('total_transport_allowance', 12, 2)->default(0);
            $table->decimal('total_lateness_penalty', 12, 2)->default(0);
            $table->decimal('total_absence_penalty', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2); // Gaji Bersih akhir
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->uuid('approved_by')->nullable(); // ID admin yang meng-approve
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();

            $table->index(['presensiable_type', 'presensiable_id'], 'salary_slip_presensiable_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_slips');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('biometric_mappings');
        Schema::dropIfExists('biometric_devices');
        Schema::dropIfExists('prayer_times');
        Schema::dropIfExists('employee_monthly_shifts');
        Schema::dropIfExists('working_shifts');
        Schema::dropIfExists('holidays');
    }
};
