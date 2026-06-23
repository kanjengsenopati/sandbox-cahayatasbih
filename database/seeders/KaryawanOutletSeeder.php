<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Karyawan;
use App\Models\Outlet;
use App\Models\EmployeeSalary;
use App\Models\WorkingShift;
use App\Models\EmployeeMonthlyShift;
use App\Models\Holiday;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use Carbon\Carbon;

class KaryawanOutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure roles exist and permissions are assigned
        $nonKasirRole = Role::firstOrCreate([
            'name' => 'Karyawan Outlet ( Non Kasir )',
            'guard_name' => 'web'
        ]);

        $kasirRole = Role::firstOrCreate([
            'name' => 'Kasir',
            'guard_name' => 'web'
        ]);

        // Ensure permissions exist
        $manageBiometric = Permission::firstOrCreate([
            'name' => 'Manage Biometric',
            'guard_name' => 'web'
        ]);

        $manageLaporanPresensi = Permission::firstOrCreate([
            'name' => 'Manage Laporan Presensi',
            'guard_name' => 'web'
        ]);

        // Assign permissions
        $nonKasirRole->syncPermissions([$manageBiometric, $manageLaporanPresensi]);
        $kasirRole->givePermissionTo($manageBiometric);

        // 2. Check Yogo to identify outlet and other default settings
        // Look up case-insensitively for YOGO
        $yogoAdmin = Admin::where('name', 'like', '%yogo%')->first();
        
        if ($yogoAdmin) {
            $this->command->info("YOGO ditemukan di database: {$yogoAdmin->name}");
            $outletId = $yogoAdmin->outlet_id;
            
            // Get Kamar & Scope from YOGO
            $yogoKaryawan = Karyawan::where('admin_id', $yogoAdmin->id)->first();
            $putraKamar = ($yogoKaryawan && $yogoKaryawan->kamar) ? $yogoKaryawan->kamar : 'B1';
            $accessScope = $yogoAdmin->access_scope ?? 'both';
            
            // Ensure YOGO is updated to uppercase and correct role
            $yogoAdmin->update([
                'name' => 'YOGO',
                'role_id' => $kasirRole->id
            ]);
            $yogoAdmin->syncRoles(['Kasir']);

            // Update YOGO Karyawan details to match production style
            if ($yogoKaryawan) {
                $yogoKaryawan->update([
                    'kamar' => $putraKamar,
                    'jabatan' => 'KASIR',
                    'section' => 'ANGKRINGAN',
                ]);
            }
        } else {
            // Local dev fallback: Create OUTLET and Yogo
            $this->command->info("YOGO tidak ditemukan. Membuat data OUTLET dan YOGO default.");
            
            $outlet = Outlet::firstOrCreate(
                ['name' => 'OUTLET'],
                [
                    'code' => 'OUT',
                    'address' => 'Pusat Outlet',
                    'is_active' => true,
                ]
            );
            $outletId = $outlet->id;
            $putraKamar = 'B1';
            $accessScope = 'both';

            // Create YOGO Admin
            $yogoAdmin = Admin::create([
                'id' => (string) Str::uuid(),
                'name' => 'YOGO',
                'email' => 'yogo@gmail.com',
                'password' => bcrypt('1234qwer'),
                'avatar' => '',
                'is_active' => true,
                'role_id' => $kasirRole->id,
                'outlet_id' => $outletId,
                'access_scope' => $accessScope,
            ]);

            $yogoAdmin->syncRoles(['Kasir']);

            \App\Models\AdminOutlet::create([
                'admin_id' => $yogoAdmin->id,
                'outlet_id' => $outletId,
            ]);

            // Create YOGO Karyawan
            Karyawan::create([
                'admin_id' => $yogoAdmin->id,
                'kamar' => 'B1',
                'jabatan' => 'KASIR',
                'outlet_id' => $outletId,
                'section' => 'ANGKRINGAN',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'potongan_terlambat' => 0,
                'potongan_absen' => 26600,
            ]);

            // Create YOGO EmployeeSalary
            EmployeeSalary::create([
                'presensiable_type' => Admin::class,
                'presensiable_id' => $yogoAdmin->id,
                'base_salary' => 800000,
                'attendance_allowance' => 0,
                'transport_allowance' => 0,
                'lateness_penalty_type' => 'fixed',
                'lateness_penalty_value' => 0,
                'lateness_penalty_per_minute' => 0,
                'absence_penalty' => 26600,
            ]);
        }

        // 3. Define employee data to seed
        $employeesData = [
            // Putri (Borealis Eatery) - Kamar C4
            [
                'name' => 'Yuka Azzahra',
                'jabatan' => 'SUPERVISOR',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 850000,
                'gaji_hari' => 70800,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Selfi Makhlihati',
                'jabatan' => 'HEAD INVENTORY',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 850000,
                'gaji_hari' => 70800,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Khurotu Aini',
                'jabatan' => 'KASIR',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 400000,
                'gaji_hari' => 41500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Kasir',
            ],
            [
                'name' => 'Rini',
                'jabatan' => 'HELPER',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Yusro',
                'jabatan' => 'HELPER',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Isfi',
                'jabatan' => 'HELPER',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Anis',
                'jabatan' => 'HELPER',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Indah',
                'jabatan' => 'HELPER',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Wawa',
                'jabatan' => 'KASIR',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Salsa',
                'jabatan' => 'HELPER',
                'establishment' => 'BOREALIS EATERY',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],

            // Putra (Angkringan) - Kamar mengikuti YOGO (B1)
            [
                'name' => 'Alam',
                'jabatan' => 'HELPER',
                'establishment' => 'ANGKRINGAN',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => $putraKamar,
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Fathkul',
                'jabatan' => 'HELPER',
                'establishment' => 'ANGKRINGAN',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 350000,
                'gaji_hari' => 29160,
                'hari_kerja' => 12,
                'kamar' => $putraKamar,
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Safik',
                'jabatan' => 'HELPER',
                'establishment' => 'ANGKRINGAN',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 350000,
                'gaji_hari' => 29160,
                'hari_kerja' => 12,
                'kamar' => $putraKamar,
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
        ];

        // Seed the other employees
        $createdAdmins = [];
        foreach ($employeesData as $empData) {
            $roleObj = $empData['role'] === 'Kasir' ? $kasirRole : $nonKasirRole;

            // Check if admin already exists by email
            $email = str_replace(' ', '', strtolower($empData['name'])) . '@gmail.com';
            $admin = Admin::where('email', $email)->first();

            if (!$admin) {
                // Ensure name is capitalized/uppercase
                $admin = Admin::create([
                    'id' => (string) Str::uuid(),
                    'name' => strtoupper($empData['name']),
                    'email' => $email,
                    'password' => bcrypt('1234qwer'),
                    'avatar' => '',
                    'is_active' => true,
                    'role_id' => $roleObj->id,
                    'outlet_id' => $outletId,
                    'access_scope' => $accessScope,
                ]);

                // Sync Spatie role
                $admin->syncRoles([$roleObj->name]);

                // Add to admin_outlets pivot
                \App\Models\AdminOutlet::create([
                    'admin_id' => $admin->id,
                    'outlet_id' => $outletId,
                ]);

                // Create Karyawan (uppercase fields to match live data style)
                Karyawan::create([
                    'admin_id' => $admin->id,
                    'kamar' => strtoupper($empData['kamar']),
                    'jabatan' => strtoupper($empData['jabatan']),
                    'outlet_id' => $outletId,
                    'section' => strtoupper($empData['establishment']),
                    'gaji_bulan' => $empData['gaji_bulan'],
                    'gaji_hari' => $empData['gaji_hari'],
                    'hari_kerja' => $empData['hari_kerja'],
                    'potongan_terlambat' => 0,
                    'potongan_absen' => $empData['gaji_hari'],
                ]);

                // Create EmployeeSalary for payroll
                EmployeeSalary::create([
                    'presensiable_type' => Admin::class,
                    'presensiable_id' => $admin->id,
                    'base_salary' => $empData['gaji_bulan'],
                    'attendance_allowance' => 0,
                    'transport_allowance' => 0,
                    'lateness_penalty_type' => 'fixed',
                    'lateness_penalty_value' => 0,
                    'lateness_penalty_per_minute' => 0,
                    'absence_penalty' => $empData['gaji_hari'],
                ]);

                $this->command->info("Karyawan {$empData['name']} berhasil ditambahkan.");
            } else {
                // If it already exists, make sure to uppercase its fields to match!
                $admin->update([
                    'name' => strtoupper($admin->name),
                    'outlet_id' => $outletId,
                    'role_id' => $roleObj->id
                ]);
                $admin->syncRoles([$roleObj->name]);

                $karyawan = Karyawan::where('admin_id', $admin->id)->first();
                if ($karyawan) {
                    $karyawan->update([
                        'kamar' => strtoupper($empData['kamar']),
                        'jabatan' => strtoupper($empData['jabatan']),
                        'section' => strtoupper($empData['establishment']),
                        'outlet_id' => $outletId,
                        'gaji_bulan' => $empData['gaji_bulan'],
                        'gaji_hari' => $empData['gaji_hari'],
                        'hari_kerja' => $empData['hari_kerja'],
                    ]);
                }
                
                $this->command->info("Karyawan {$empData['name']} (Email: {$email}) sudah ada. Melakukan update data.");
            }

            $createdAdmins[] = [
                'admin' => $admin,
                'section' => $empData['section']
            ];
        }

        // 4. Create Default Working Shifts if not exists
        $weekdaysShift = WorkingShift::firstOrCreate(
            ['name' => 'Weekdays'],
            [
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'grace_period' => 15,
                'target_type' => 'karyawan',
                'days' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'],
                'is_active' => true,
            ]
        );

        $weekendShift = WorkingShift::firstOrCreate(
            ['name' => 'Weekend & Event'],
            [
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'grace_period' => 15,
                'target_type' => 'karyawan',
                'days' => ['Sabtu', 'Minggu'],
                'is_active' => true,
            ]
        );

        // Assign users to shifts
        $weekdaysAssigned = [];
        $weekendAssigned = [];

        // Include YOGO in Weekdays
        if ($yogoAdmin) {
            $weekdaysAssigned[] = "App\\Models\\Admin:" . $yogoAdmin->id;
        }

        foreach ($createdAdmins as $item) {
            $userStr = "App\\Models\\Admin:" . $item['admin']->id;
            if ($item['section'] === 'Weekdays') {
                $weekdaysAssigned[] = $userStr;
            } else {
                $weekendAssigned[] = $userStr;
            }
        }

        // Update assigned users in working shifts
        $weekdaysShift->update(['assigned_users' => $weekdaysAssigned]);
        $weekendShift->update(['assigned_users' => $weekendAssigned]);

        $this->command->info("Working shifts assigned users updated.");

        // 5. Sync Employee Monthly Shifts for current and next month
        $this->syncMonthlyShifts($weekdaysShift);
        $this->syncMonthlyShifts($weekendShift);

        $this->command->info("Monthly shifts synced successfully.");
    }

    /**
     * Replicates the monthly shift sync logic.
     */
    private function syncMonthlyShifts(WorkingShift $workingShift): void
    {
        $activeDays = $workingShift->days ?? [];
        if (empty($activeDays)) {
            return;
        }

        $dayMap = [
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
            'Minggu' => 7,
        ];

        $activeDayNumbers = array_map(function($day) use ($dayMap) {
            return $dayMap[$day] ?? null;
        }, $activeDays);
        $activeDayNumbers = array_filter($activeDayNumbers);

        // Sync for current month and next month
        $startDate = now()->startOfMonth();
        $endDate = now()->addMonth()->endOfMonth();

        // Clear existing monthly shifts to prevent duplicates
        EmployeeMonthlyShift::where('working_shift_id', $workingShift->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->delete();

        $assignedUsers = $workingShift->assigned_users ?? [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeekIso;
            
            if (in_array($dayOfWeek, $activeDayNumbers)) {
                $isHoliday = Holiday::where('date', $date->toDateString())->exists();

                foreach ($assignedUsers as $userStr) {
                    $parts = explode(':', $userStr);
                    if (count($parts) === 2) {
                        $type = $parts[0];
                        $id = $parts[1];

                        EmployeeMonthlyShift::updateOrCreate(
                            [
                                'presensiable_type' => $type,
                                'presensiable_id' => $id,
                                'date' => $date->toDateString(),
                                'working_shift_id' => $workingShift->id,
                            ],
                            [
                                'is_holiday' => $isHoliday,
                            ]
                        );
                    }
                }
            }
        }
    }
}
