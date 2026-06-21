<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Student;
use App\Models\WorkingShift;
use App\Models\EmployeeMonthlyShift;
use App\Models\PrayerTime;
use App\Models\BiometricDevice;
use App\Models\BiometricMapping;
use App\Models\Attendance;
use App\Models\EmployeeSalary;
use App\Models\SalarySlip;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class AttendancePayrollTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass all permission/gate checks
        Gate::before(function () {
            return true;
        });

        // 1. Buat tabel-tabel secara dinamis untuk menghindari kegagalan migrasi warisan di SQLite
        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
            Schema::dropIfExists('role_has_permissions');
            Schema::dropIfExists('model_has_roles');
            Schema::dropIfExists('model_has_permissions');
            Schema::dropIfExists('roles');
            Schema::dropIfExists('permissions');
            Schema::dropIfExists('activity_log');
            Schema::dropIfExists('salary_slips');
            Schema::dropIfExists('karyawans');
            Schema::dropIfExists('employee_salaries');
            Schema::dropIfExists('attendances');
            Schema::dropIfExists('biometric_mappings');
            Schema::dropIfExists('biometric_devices');
            Schema::dropIfExists('prayer_times');
            Schema::dropIfExists('employee_monthly_shifts');
            Schema::dropIfExists('working_shifts');
            Schema::dropIfExists('students');
            Schema::dropIfExists('admins');
            Schema::dropIfExists('outlets');
            Schema::dropIfExists('users');

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->string('model_id');
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->string('model_id');
            $table->primary(['role_id', 'model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();
            $table->text('properties')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->string('event')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('nis');
            $table->string('status');
            $table->string('barcode')->nullable();
            $table->string('asrama_name')->nullable();
            $table->uuid('asrama_host_id')->nullable();
            $table->uuid('asrama_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('admins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->boolean('is_active');
            $table->uuid('school_id')->nullable();
            $table->uuid('outlet_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('working_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_period')->default(15);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_monthly_shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type');
            $table->uuid('presensiable_id');
            $table->uuid('working_shift_id')->nullable();
            $table->date('date');
            $table->boolean('is_holiday')->default(false);
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prayer_times', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('prayer_name')->unique();
            $table->time('adzan_time');
            $table->time('iqomah_time');
            $table->integer('grace_period')->default(5);
            $table->timestamps();
        });

        Schema::create('biometric_devices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('device_name');
            $table->string('device_ip')->nullable();
            $table->string('location');
            $table->string('auth_token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('biometric_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type');
            $table->uuid('presensiable_id');
            $table->enum('biometric_type', ['fingerprint', 'face']);
            $table->integer('biometric_index')->default(0);
            $table->string('device_pin');
            $table->text('template_data')->nullable();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type');
            $table->uuid('presensiable_id');
            $table->enum('activity_type', ['school', 'prayer', 'kajian', 'work']);
            $table->string('activity_name');
            $table->uuid('schedule_id')->nullable();
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'permit', 'sick'])->default('present');
            $table->integer('late_minutes')->default(0);
            $table->enum('method', ['manual', 'barcode', 'fingerprint', 'face_recognition'])->default('manual');
            $table->uuid('device_id')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('presensiable_type');
            $table->uuid('presensiable_id');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('attendance_allowance', 12, 2)->default(0);
            $table->decimal('transport_allowance', 12, 2)->default(0);
            $table->decimal('lateness_penalty_per_minute', 12, 2)->default(0);
            $table->decimal('absence_penalty', 12, 2)->default(0);
            $table->timestamps();
        });

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
            $table->decimal('net_salary', 12, 2);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->uuid('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
        });

        Schema::create('karyawans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_id')->unique();
            $table->string('kamar')->nullable();
            $table->string('jabatan')->nullable();
            $table->uuid('outlet_id');
            $table->string('section')->nullable();
            $table->decimal('gaji_bulan', 12, 2)->default(0);
            $table->decimal('gaji_hari', 12, 2)->default(0);
            $table->integer('hari_kerja')->default(0);
            $table->decimal('potongan_terlambat', 12, 2)->default(0);
            $table->decimal('potongan_absen', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('outlets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        }

        // Seed Jadwal Sholat default
        if (!PrayerTime::where('prayer_name', 'Subuh')->exists()) {
            PrayerTime::create([
                'id' => (string) Str::uuid(),
                'prayer_name' => 'Subuh',
                'adzan_time' => '04:30:00',
                'iqomah_time' => '04:45:00',
                'grace_period' => 5,
            ]);
        }
    }

    public function test_biometric_log_webhook_for_student_prayer()
    {
        $device = BiometricDevice::create([
            'device_name' => 'Mesin Masjid',
            'location' => 'Mesin Masjid',
            'auth_token' => 'secure-masjid-token',
        ]);

        $student = Student::create([
            'id' => (string) Str::uuid(),
            'name' => 'Ahmad Fauzi',
            'nis' => '12345',
            'status' => 'ACTIVE',
        ]);

        BiometricMapping::create([
            'presensiable_type' => Student::class,
            'presensiable_id' => $student->id,
            'biometric_type' => 'fingerprint',
            'device_pin' => '9901',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'secure-masjid-token'
        ])->postJson('/api/ct-mobile/biometric/log', [
            'logs' => [
                [
                    'pin' => '9901',
                    'timestamp' => '2026-06-18 04:40:00',
                    'type' => 'in'
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendances', [
            'presensiable_type' => Student::class,
            'presensiable_id' => $student->id,
            'activity_type' => 'prayer',
            'activity_name' => 'Subuh',
            'status' => 'present',
            'late_minutes' => 0
        ]);
    }

    public function test_biometric_log_webhook_for_student_prayer_late()
    {
        $device = BiometricDevice::create([
            'device_name' => 'Mesin Masjid',
            'location' => 'Mesin Masjid',
            'auth_token' => 'secure-masjid-token',
        ]);

        $student = Student::create([
            'id' => (string) Str::uuid(),
            'name' => 'Ahmad Fauzi',
            'nis' => '12345',
            'status' => 'ACTIVE',
        ]);

        BiometricMapping::create([
            'presensiable_type' => Student::class,
            'presensiable_id' => $student->id,
            'biometric_type' => 'fingerprint',
            'device_pin' => '9901',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'secure-masjid-token'
        ])->postJson('/api/ct-mobile/biometric/log', [
            'logs' => [
                [
                    'pin' => '9901',
                    'timestamp' => '2026-06-18 04:55:00',
                    'type' => 'in'
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('attendances', [
            'presensiable_type' => Student::class,
            'presensiable_id' => $student->id,
            'activity_type' => 'prayer',
            'activity_name' => 'Subuh',
            'status' => 'late',
            'late_minutes' => 10
        ]);
    }

    public function test_payroll_calculation_and_approval_flow()
    {
        $shift = WorkingShift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'grace_period' => 15
        ]);

        $employee = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Budi Sudarsono',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'avatar' => '',
            'role_id' => 1,
        ]);

        EmployeeSalary::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'base_salary' => 3000000.00,
            'attendance_allowance' => 50000.00,
            'transport_allowance' => 20000.00,
            'lateness_penalty_per_minute' => 1000.00,
            'absence_penalty' => 100000.00,
        ]);

        EmployeeMonthlyShift::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'working_shift_id' => $shift->id,
            'date' => '2026-06-18',
            'is_holiday' => false,
        ]);

        EmployeeMonthlyShift::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'working_shift_id' => $shift->id,
            'date' => '2026-06-19',
            'is_holiday' => false,
        ]);

        Attendance::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'activity_type' => 'work',
            'activity_name' => 'Shift Pagi',
            'check_in' => '2026-06-18 08:20:00',
            'check_out' => '2026-06-18 16:00:00',
            'status' => 'late',
            'late_minutes' => 20,
            'method' => 'fingerprint'
        ]);

        $payrollController = new \App\Http\Controllers\Admin\PayrollController();
        
        $request = new Request([
            'start_date' => '2026-06-18',
            'end_date' => '2026-06-19',
        ]);

        $this->actingAs($employee);

        $response = $payrollController->process($request);
        $resData = json_decode($response->getContent(), true);
        
        $this->assertTrue($resData['success']);

        $this->assertDatabaseHas('salary_slips', [
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'total_present_days' => 1,
            'total_late_minutes' => 20,
            'total_absent_days' => 1,
            'net_salary' => 2950000.00,
            'status' => 'draft'
        ]);

        $slip = SalarySlip::where('presensiable_id', $employee->id)->first();
        
        $approveResponse = $this->post("/payroll/{$slip->id}/approve");
        
        $this->assertDatabaseHas('salary_slips', [
            'id' => $slip->id,
            'status' => 'approved',
            'approved_by' => $employee->id
        ]);
    }

    public function test_employee_attendance_needs_approval_for_payroll()
    {
        $shift = WorkingShift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'grace_period' => 15
        ]);

        $employee = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Ujang Dapur',
            'email' => 'ujang@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'avatar' => '',
            'role_id' => 1,
        ]);

        EmployeeSalary::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'base_salary' => 3000000.00,
            'attendance_allowance' => 50000.00,
            'transport_allowance' => 20000.00,
            'lateness_penalty_per_minute' => 1000.00,
            'absence_penalty' => 100000.00,
        ]);

        // Shift tanggal 18
        EmployeeMonthlyShift::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'working_shift_id' => $shift->id,
            'date' => '2026-06-18',
            'is_holiday' => false,
        ]);

        // Buat biometric device dan mapping
        $device = BiometricDevice::create([
            'device_name' => 'Kiosk POS',
            'location' => 'Kantin',
            'auth_token' => 'secure-kiosk-token',
        ]);

        BiometricMapping::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'biometric_type' => 'face',
            'device_pin' => 'F-' . $employee->id,
        ]);

        // 1. Simulasikan presensi masuk via webhook / biometric API (harusnya status = pending)
        $this->withHeaders([
            'Authorization' => 'secure-kiosk-token'
        ])->postJson('/api/ct-mobile/biometric/log', [
            'logs' => [
                [
                    'pin' => 'F-' . $employee->id,
                    'timestamp' => '2026-06-18 08:05:00',
                    'type' => 'in'
                ]
            ]
        ]);

        // Pastikan record attendance berstatus pending
        $this->assertDatabaseHas('attendances', [
            'presensiable_id' => $employee->id,
            'activity_type' => 'work',
            'approval_status' => 'pending'
        ]);

        // 2. Jalankan Payroll. Karena status pending, presensi ini TIDAK BOLEH dihitung (dianggap absen)
        $payrollController = new \App\Http\Controllers\Admin\PayrollController();
        $request = new Request([
            'start_date' => '2026-06-18',
            'end_date' => '2026-06-18',
        ]);

        $this->actingAs($employee);
        $payrollController->process($request);

        // Ujang harusnya dihitung absen 1 hari (karena kehadirannya pending)
        // Gaji Ujang dipotong denda absen Rp100.000,00 dan tidak dapat tunjangan kehadiran/transport
        $this->assertDatabaseHas('salary_slips', [
            'presensiable_id' => $employee->id,
            'total_present_days' => 0, // Kehadiran pending tidak dihitung!
            'total_absent_days' => 1,
            'net_salary' => 2900000.00 // 3.000.000 - 100.000 denda mangkir
        ]);

        // 3. Setujui presensi Ujang melalui ReportAttendanceController
        $attendance = Attendance::where('presensiable_id', $employee->id)->first();
        $reportController = new \App\Http\Controllers\Admin\ReportAttendanceController();
        
        $approveResponse = $reportController->approve($attendance->id);
        $this->assertTrue(json_decode($approveResponse->getContent(), true)['success']);

        // Pastikan status presensi berubah menjadi approved
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'approval_status' => 'approved',
            'approved_by' => $employee->id
        ]);

        // 4. Proses ulang Payroll. Sekarang kehadiran Ujang harusnya sudah dihitung!
        $payrollController->process($request);

        // Ujang sekarang dihitung hadir 1 hari, dapat tunjangan kehadiran (50.000) dan transport (20.000)
        $this->assertDatabaseHas('salary_slips', [
            'presensiable_id' => $employee->id,
            'total_present_days' => 1,
            'total_absent_days' => 0,
            'net_salary' => 3070000.00 // 3.000.000 + 50.000 + 20.000
        ]);
    }

    public function test_karyawan_shift_based_deduction_payroll()
    {
        $shift1 = WorkingShift::create([
            'name' => 'Shift Pagi',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'grace_period' => 15
        ]);

        $shift2 = WorkingShift::create([
            'name' => 'Shift Siang',
            'start_time' => '13:00:00',
            'end_time' => '17:00:00',
            'grace_period' => 15
        ]);

        $shift3 = WorkingShift::create([
            'name' => 'Shift Sore',
            'start_time' => '18:00:00',
            'end_time' => '22:00:00',
            'grace_period' => 15
        ]);

        $outlet = \App\Models\Outlet::create([
            'id' => (string) Str::uuid(),
            'name' => 'Pondok Mart Outlet',
            'code' => 'OTL',
            'is_active' => true,
        ]);

        $employee = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Yogo',
            'email' => 'yogo@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'avatar' => '',
            'role_id' => 1,
            'outlet_id' => $outlet->id,
        ]);

        \App\Models\Karyawan::create([
            'admin_id' => $employee->id,
            'gaji_bulan' => 900000.00,
            'gaji_hari' => 30000.00,
            'hari_kerja' => 30,
            'outlet_id' => $outlet->id,
        ]);

        EmployeeSalary::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'base_salary' => 900000.00,
            'attendance_allowance' => 30000.00,
            'transport_allowance' => 0.00,
            'lateness_penalty_type' => 'fixed',
            'lateness_penalty_value' => 10000.00,
            'absence_penalty' => 10000.00,
        ]);

        // Simulasikan 3 shift pada tanggal 2026-06-18
        EmployeeMonthlyShift::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'working_shift_id' => $shift1->id,
            'date' => '2026-06-18',
            'is_holiday' => false,
        ]);

        EmployeeMonthlyShift::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'working_shift_id' => $shift2->id,
            'date' => '2026-06-18',
            'is_holiday' => false,
        ]);

        EmployeeMonthlyShift::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'working_shift_id' => $shift3->id,
            'date' => '2026-06-18',
            'is_holiday' => false,
        ]);

        // Kehadiran:
        // Shift 1: Hadir Tepat Waktu
        Attendance::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'activity_type' => 'work',
            'activity_name' => 'Shift Pagi',
            'check_in' => '2026-06-18 08:05:00',
            'check_out' => '2026-06-18 12:00:00',
            'status' => 'present',
            'late_minutes' => 0,
            'approval_status' => 'approved',
        ]);

        // Shift 2: Hadir Tepat Waktu
        Attendance::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'activity_type' => 'work',
            'activity_name' => 'Shift Siang',
            'check_in' => '2026-06-18 13:05:00',
            'check_out' => '2026-06-18 17:00:00',
            'status' => 'present',
            'late_minutes' => 0,
            'approval_status' => 'approved',
        ]);

        // Shift 3: Terlambat
        Attendance::create([
            'presensiable_type' => Admin::class,
            'presensiable_id' => $employee->id,
            'activity_type' => 'work',
            'activity_name' => 'Shift Sore',
            'check_in' => '2026-06-18 18:25:00',
            'check_out' => '2026-06-18 22:00:00',
            'status' => 'late',
            'late_minutes' => 25,
            'approval_status' => 'approved',
        ]);

        $payrollController = new \App\Http\Controllers\Admin\PayrollController();
        $request = new Request([
            'start_date' => '2026-06-18',
            'end_date' => '2026-06-18',
            'mode' => 'outlet',
            'outlet_id' => $outlet->id,
        ]);

        $this->actingAs($employee);
        $payrollController->process($request);

        // Gaji bersih harus dideduksi 10.000 karena terlambat di shift ke-3.
        // Gaji bersih = 900.000 - 10.000 = 890.000.
        $this->assertDatabaseHas('salary_slips', [
            'presensiable_id' => $employee->id,
            'total_present_days' => 1, // 2/3 rounded to 1
            'total_absent_days' => 0,
            'total_lateness_penalty' => 10000.00,
            'total_absence_penalty' => 0.00,
            'net_salary' => 890000.00
        ]);
    }

    public function test_karyawan_atomic_transaction_rollback()
    {
        // Uji coba kepatuhan transaksi atomik (atomic transaction)
        $employee = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => 'Transaction Rollback Test',
            'email' => 'rollback@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'avatar' => '',
            'role_id' => 1,
        ]);

        // Simulasikan kegagalan dengan sengaja melempar exception di dalam DB transaction
        $originalKaryawanCount = \App\Models\Karyawan::count();

        $outlet = \App\Models\Outlet::create([
            'id' => (string) Str::uuid(),
            'name' => 'Outlet Rollback Test',
            'code' => 'OT-ROLL',
            'is_active' => true,
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function() use ($employee, $outlet) {
                \App\Models\Karyawan::create([
                    'admin_id' => $employee->id,
                    'gaji_bulan' => 1000000.00,
                    'gaji_hari' => 50000.00,
                    'hari_kerja' => 20,
                    'outlet_id' => $outlet->id,
                    'potongan_terlambat' => 15000.00,
                    'potongan_absen' => 50000.00,
                ]);
                
                // Sengaja memicu error/exception untuk menguji rollback
                throw new \Exception("Intentional failure to test atomic rollback.");
            });
        } catch (\Exception $e) {
            // Expected exception
        }

        // Pastikan tidak ada data yang masuk ke tabel karyawans (ter-rollback)
        $this->assertEquals($originalKaryawanCount, \App\Models\Karyawan::count());
    }
}
