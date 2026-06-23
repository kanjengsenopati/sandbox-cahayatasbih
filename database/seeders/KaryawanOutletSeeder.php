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
        // 1. Ensure outlets exist
        $borealis = Outlet::firstOrCreate(
            ['name' => 'Borealis Eatery'],
            [
                'code' => 'BE',
                'address' => 'Borealis Eatery',
                'is_active' => true,
            ]
        );

        $angkringan = Outlet::firstOrCreate(
            ['name' => 'Angkringan'],
            [
                'code' => 'AK',
                'address' => 'Angkringan',
                'is_active' => true,
            ]
        );

        // 2. Ensure roles exist and permissions are assigned
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

        // 3. Define employee data
        $employeesData = [
            // Putri (Borealis Eatery) - Kamar C4
            [
                'name' => 'Yuka Azzahra',
                'jabatan' => 'Supervisor',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 850000,
                'gaji_hari' => 70800,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Selfi Makhlihati',
                'jabatan' => 'Head Inventory',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 850000,
                'gaji_hari' => 70800,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Khurotu Aini',
                'jabatan' => 'Cashier',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 400000,
                'gaji_hari' => 41500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Kasir',
            ],
            [
                'name' => 'Rini',
                'jabatan' => 'Helper',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Yusro',
                'jabatan' => 'Helper',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Isfi',
                'jabatan' => 'Helper',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Anis',
                'jabatan' => 'Helper',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Indah',
                'jabatan' => 'Helper',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 200000,
                'gaji_hari' => 16500,
                'hari_kerja' => 12,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Wawa',
                'jabatan' => 'Cashier',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Salsa',
                'jabatan' => 'Helper',
                'outlet' => 'Borealis Eatery',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => 'C4',
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],

            // Putra (Angkringan) - No Kamar
            [
                'name' => 'Alam',
                'jabatan' => 'Helper',
                'outlet' => 'Angkringan',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => null,
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Fathkul',
                'jabatan' => 'Helper',
                'outlet' => 'Angkringan',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 350000,
                'gaji_hari' => 29160,
                'hari_kerja' => 12,
                'kamar' => null,
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
            [
                'name' => 'Safik',
                'jabatan' => 'Helper',
                'outlet' => 'Angkringan',
                'section' => 'Weekend & Event',
                'gaji_bulan' => 350000,
                'gaji_hari' => 29160,
                'hari_kerja' => 12,
                'kamar' => null,
                'role' => 'Karyawan Outlet ( Non Kasir )',
            ],
        ];

        // 4. Handle YOGO/Yogo checks
        $yogoQuery = Admin::where('name', 'like', '%Yogo%');
        if ($yogoQuery->exists()) {
            $yogoAdmin = $yogoQuery->first();
            $this->command->info("Yogo sudah ada di database. Dilewati.");
        } else {
            // Yogo does not exist, seed Yogo as well (primarily for testing locally)
            $yogoAdmin = $this->createEmployee([
                'name' => 'Yogo',
                'jabatan' => 'Cashier',
                'outlet' => 'Angkringan',
                'section' => 'Weekdays',
                'gaji_bulan' => 800000,
                'gaji_hari' => 26600,
                'hari_kerja' => 30,
                'kamar' => null,
                'role' => 'Kasir',
            ], $angkringan->id, $kasirRole);
            $this->command->info("Yogo tidak ditemukan. Membuat Yogo sebagai data awal.");
        }

        // Seed the other employees
        $createdAdmins = [];
        foreach ($employeesData as $empData) {
            $outletId = $empData['outlet'] === 'Borealis Eatery' ? $borealis->id : $angkringan->id;
            $roleObj = $empData['role'] === 'Kasir' ? $kasirRole : $nonKasirRole;

            // Check if admin already exists by email
            $email = str_replace(' ', '', strtolower($empData['name'])) . '@gmail.com';
            $admin = Admin::where('email', $email)->first();

            if (!$admin) {
                $admin = $this->createEmployee($empData, $outletId, $roleObj);
                $this->command->info("Karyawan {$empData['name']} berhasil ditambahkan.");
            } else {
                $this->command->info("Karyawan {$empData['name']} (Email: {$email}) sudah ada. Dilewati.");
            }
            $createdAdmins[] = [
                'admin' => $admin,
                'section' => $empData['section']
            ];
        }

        // 5. Create Default Working Shifts if not exists
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
        // Format of assigned_users: Array of strings "App\Models\Admin:{uuid}"
        $weekdaysAssigned = [];
        $weekendAssigned = [];

        // Include Yogo in Weekdays
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

        // 6. Sync Employee Monthly Shifts for current and next month
        $this->syncMonthlyShifts($weekdaysShift);
        $this->syncMonthlyShifts($weekendShift);

        $this->command->info("Monthly shifts synced successfully.");
    }

    /**
     * Create an Admin, Karyawan and EmployeeSalary record.
     */
    private function createEmployee(array $empData, string $outletId, Role $role): Admin
    {
        $email = str_replace(' ', '', strtolower($empData['name'])) . '@gmail.com';
        
        $admin = Admin::create([
            'id' => (string) Str::uuid(),
            'name' => $empData['name'],
            'email' => $email,
            'password' => bcrypt('1234qwer'),
            'avatar' => '',
            'is_active' => true,
            'role_id' => $role->id,
            'outlet_id' => $outletId,
            'access_scope' => 'both',
        ]);

        // Sync Spatie role
        $admin->syncRoles([$role->name]);

        // Add to admin_outlets pivot
        \App\Models\AdminOutlet::create([
            'admin_id' => $admin->id,
            'outlet_id' => $outletId,
        ]);

        // Create Karyawan
        Karyawan::create([
            'admin_id' => $admin->id,
            'kamar' => $empData['kamar'],
            'jabatan' => $empData['jabatan'],
            'outlet_id' => $outletId,
            'section' => $empData['section'],
            'gaji_bulan' => $empData['gaji_bulan'],
            'gaji_hari' => $empData['gaji_hari'],
            'hari_kerja' => $empData['hari_kerja'],
            'potongan_terlambat' => 0,
            'potongan_absen' => $empData['gaji_hari'],
        ]);

        // Create EmployeeSalary for payroll integration
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

        return $admin;
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
