<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuNavigation;
use App\Models\SubMenuNavigation;

class MenuNavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menus
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        SubMenuNavigation::query()->delete();
        MenuNavigation::query()->delete();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Dashboard
        $dashboard = MenuNavigation::create([
            'name' => 'Dashboard',
            'icon' => 'fa-solid fa-house',
            'url' => '/dashboard',
            'order' => 1,
            'is_active' => true,
            'permission' => null,
        ]);

        // 2. Akademik
        $akademik = MenuNavigation::create([
            'name' => 'Akademik',
            'icon' => 'fa-solid fa-graduation-cap',
            'url' => '/academic',
            'order' => 2,
            'is_active' => true,
            'permission' => 'Manage Sekolah,Manage Tahun Ajaran,Manage Semester,Manage Mata Pelajaran,Manage Kenaikan Kelas,Manage Kelulusan Santri',
        ]);

        // 3. Pondok Mart & Koperasi (Unit Usaha)
        $pondokMart = MenuNavigation::create([
            'name' => 'Pondok Mart & Koperasi',
            'icon' => 'fa-solid fa-store',
            'url' => null,
            'order' => 3,
            'is_active' => true,
            'permission' => 'Manage Outlet,Manage Barang,Manage Pos Kasir,Manage Laporan Pos Kasir,Manage Laporan Pos Multi Outlet,Manage Laporan Rugi Laba,Manage Karyawan,Manage Shift,Manage Laporan Presensi,Manage Biometric',
        ]);

        $subPondokMart = [
            ['name' => 'POS Kasir', 'url' => '/order-item?mode=outlet', 'permission' => 'Manage Pos Kasir', 'order' => 1],
            ['name' => 'Barang & Inventory', 'url' => '/item?mode=outlet', 'permission' => 'Manage Barang', 'order' => 2],
            ['name' => 'Laporan POS', 'url' => '/pos-transaction?mode=outlet', 'permission' => 'Manage Laporan Pos Multi Outlet', 'order' => 3],
            ['name' => 'Rugi Laba', 'url' => '/report-profit-loss?mode=outlet', 'permission' => 'Manage Laporan Rugi Laba', 'order' => 4],
            ['name' => 'Data Karyawan & Payroll', 'url' => '/karyawan?mode=outlet', 'permission' => 'Manage Karyawan,Manage Shift', 'order' => 5],
            ['name' => 'Presensi Karyawan', 'url' => '/report-attendance?mode=outlet', 'permission' => 'Manage Laporan Presensi', 'order' => 6],
            ['name' => 'Kiosk Presensi Wajah', 'url' => '/biometric-mapping/kiosk', 'permission' => 'Manage Biometric', 'order' => 7],
            ['name' => 'Data Outlet', 'url' => '/outlet', 'permission' => 'Manage Outlet', 'order' => 8],
        ];

        foreach ($subPondokMart as $sub) {
            $pondokMart->subMenuNavigation()->create([
                'name' => $sub['name'],
                'url' => $sub['url'],
                'permission' => $sub['permission'],
                'order' => $sub['order'],
                'is_active' => true,
            ]);
        }

        // 4. Master Data
        $masterData = MenuNavigation::create([
            'name' => 'Master Data',
            'icon' => 'fa-solid fa-school',
            'url' => null,
            'order' => 4,
            'is_active' => true,
            'permission' => 'Manage Wali Santri,Manage Santri,Manage Outlet,Manage PPDB,Manage Kategori Arus Kas',
        ]);

        $subMasterData = [
            ['name' => 'Data Wali Siswa', 'url' => '/user', 'permission' => 'Manage Wali Santri', 'order' => 1],
            ['name' => 'Data Siswa', 'url' => '/student', 'permission' => 'Manage Santri', 'order' => 2],
            ['name' => 'Data Outlet', 'url' => '/outlet', 'permission' => 'Manage Outlet', 'order' => 3],
            ['name' => 'Data PPDB', 'url' => '/ppdb', 'permission' => 'Manage PPDB', 'order' => 4],
            ['name' => 'Kategori Arus Kas', 'url' => '/cashflow-category', 'permission' => 'Manage Kategori Arus Kas', 'order' => 5],
        ];

        foreach ($subMasterData as $sub) {
            $masterData->subMenuNavigation()->create([
                'name' => $sub['name'],
                'url' => $sub['url'],
                'permission' => $sub['permission'],
                'order' => $sub['order'],
                'is_active' => true,
            ]);
        }

        // 5. Entri Data
        $entriData = MenuNavigation::create([
            'name' => 'Entri Data',
            'icon' => 'fa-solid fa-edit',
            'url' => null,
            'order' => 5,
            'is_active' => true,
            'permission' => 'Manage Saldo Santri,Manage Tabungan Santri,Manage Jadwal,Manage Tahfidz,Manage Tagihan,Manage Perilaku Santri,Manage Nilai Pelajaran,Manage Prestasi Santri,Manage Nilai Santri,Manage Arus Kas,Manage Perizinan,Manage Payroll',
        ]);

        $subEntriData = [
            ['name' => 'Tagihan', 'url' => '/bill', 'permission' => 'Manage Tagihan', 'order' => 1],
            ['name' => 'Saldo Santri', 'url' => '/saldo-history', 'permission' => 'Manage Saldo Santri', 'order' => 2],
            ['name' => 'Tabungan Santri', 'url' => '/saving-history', 'permission' => 'Manage Tabungan Santri', 'order' => 3],
            ['name' => 'Arus Kas', 'url' => '/cashflow', 'permission' => 'Manage Arus Kas', 'order' => 4],
            ['name' => 'Perizinan Santri', 'url' => '/student-permit', 'permission' => 'Manage Perizinan', 'order' => 5],
            ['name' => 'Payroll Koperasi', 'url' => '/payroll?mode=kantin', 'permission' => 'Manage Payroll', 'order' => 6],
        ];

        foreach ($subEntriData as $sub) {
            $entriData->subMenuNavigation()->create([
                'name' => $sub['name'],
                'url' => $sub['url'],
                'permission' => $sub['permission'],
                'order' => $sub['order'],
                'is_active' => true,
            ]);
        }

        // 6. Laporan
        $laporan = MenuNavigation::create([
            'name' => 'Laporan',
            'icon' => 'fa-solid fa-file',
            'url' => null,
            'order' => 6,
            'is_active' => true,
            'permission' => 'Manage Laporan Tagihan,Manage Laporan Santri,Manage Laporan Nilai Akademik,Manage Laporan Tahfidz,Manage Laporan Perilaku Siswa,Manage Laporan Saldo Santri,Manage Laporan Fee Aplikasi,Manage Laporan Transaksi,Manage Laporan Audit Log,Manage Laporan Presensi',
        ]);

        $subLaporan = [
            ['name' => 'Transaksi', 'url' => '/report-transaction', 'permission' => 'Manage Laporan Transaksi', 'order' => 1],
            ['name' => 'Tagihan', 'url' => '/report-bill', 'permission' => 'Manage Laporan Tagihan', 'order' => 2],
            ['name' => 'Tagihan Santri', 'url' => '/report-bill-student', 'permission' => 'Manage Laporan Tagihan', 'order' => 3],
            ['name' => 'Kehadiran Siswa', 'url' => '/report-attendance?mode=kantin', 'permission' => 'Manage Laporan Presensi', 'order' => 4],
            ['name' => 'Rapor Siswa', 'url' => '/report-student', 'permission' => 'Manage Laporan Santri', 'order' => 5],
            ['name' => 'Catatan Wali Kelas', 'url' => '/report-student-counseling-score', 'permission' => 'Manage Laporan Perilaku Siswa', 'order' => 6],
            ['name' => 'Tabungan & Saldo', 'url' => '/report-saldo', 'permission' => 'Manage Laporan Saldo Santri', 'order' => 7],
            ['name' => 'Fee Aplikasi', 'url' => '/report-app-fee', 'permission' => 'Manage Laporan Fee Aplikasi', 'order' => 8],
            ['name' => 'Audit Log', 'url' => '/report-audit', 'permission' => 'Manage Laporan Audit Log', 'order' => 9],
        ];

        foreach ($subLaporan as $sub) {
            $laporan->subMenuNavigation()->create([
                'name' => $sub['name'],
                'url' => $sub['url'],
                'permission' => $sub['permission'],
                'order' => $sub['order'],
                'is_active' => true,
            ]);
        }

        // 7. Menu Pengaturan
        $pengaturan = MenuNavigation::create([
            'name' => 'Menu Pengaturan',
            'icon' => 'fa-solid fa-cog',
            'url' => null,
            'order' => 7,
            'is_active' => true,
            'permission' => 'permission,Manage Role,Manage Admin,Manage Informasi,Manage Metode Pembayaran,Manage Menu Aplikasi,Manage Kontak Bantuan,Manage Bank,Manage Pengaturan Aplikasi,Item Bayar,Manage Item Bayar,Manage Jenis Bayar,Manage Petugas,app-information,Manage Kartu Santri,Manage Kartu Ujian',
        ]);

        $subPengaturan = [
            ['name' => 'Akses & Pengguna', 'url' => '/admin', 'permission' => 'permission,Manage Role,Manage Admin', 'order' => 1],
            ['name' => 'Keuangan & Bank', 'url' => '/bill-type', 'permission' => 'Manage Jenis Bayar,Manage Item Bayar,Item Bayar,Manage Bank', 'order' => 2],
            ['name' => 'Pengaturan Aplikasi', 'url' => '/application-setting', 'permission' => 'Manage Pengaturan Aplikasi,Manage Menu Aplikasi', 'order' => 3],
            ['name' => 'Kartu', 'url' => '/student-card-setting', 'permission' => 'Manage Kartu Santri,Manage Kartu Ujian', 'order' => 4],
            ['name' => 'Manajemen Informasi', 'url' => '/information', 'permission' => 'Manage Informasi', 'order' => 5],
            ['name' => 'Metode Pembayaran', 'url' => '/payment-method', 'permission' => 'Manage Metode Pembayaran', 'order' => 6],
            ['name' => 'Menu Aplikasi Wali', 'url' => '/application-menu', 'permission' => 'Manage Menu Aplikasi', 'order' => 7],
            ['name' => 'Kontak Bantuan', 'url' => '/help', 'permission' => 'Manage Kontak Bantuan', 'order' => 8],
            ['name' => 'Petugas', 'url' => '/officer', 'permission' => 'Manage Petugas', 'order' => 9],
            ['name' => 'Informasi Aplikasi', 'url' => '/app-information', 'permission' => 'app-information', 'order' => 10],
            ['name' => 'Menu Backoffice', 'url' => '/menu-navigation', 'permission' => null, 'order' => 11],
        ];

        foreach ($subPengaturan as $sub) {
            $pengaturan->subMenuNavigation()->create([
                'name' => $sub['name'],
                'url' => $sub['url'],
                'permission' => $sub['permission'],
                'order' => $sub['order'],
                'is_active' => true,
            ]);
        }
    }
}
