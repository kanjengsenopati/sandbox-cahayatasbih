# Handoff Report: Implementasi Sub Menu "Arus Kas Mart" pada Menu Laporan

## 1. Ringkasan Pekerjaan
Telah diimplementasikan fitur sub menu **"Arus Kas Mart"** pada menu navigasi **Laporan** untuk menyajikan rekapitulasi dan riwayat transaksi belanja siswa santri yang pembayarannya menggunakan metode **Saldo**. Fitur ini memungkinkan pengelola membedakan dan menyaring transaksi antara **Pondok Mart / Koperasi** dan unit **Outlet**, sehingga transaksi belanja via saldo di outlet dapat direkonsiliasi dan ditagihkan secara tepat kepada pengelola Pondok Mart / Koperasi selaku Bank Saldo.

Branch Git: `feat/siswanto/laporan-arus-kas-mart`

## 2. File yang Dibuat / Dimodifikasi
1. **`app/Services/MartUserScopeService.php`** (Baru):
   - Service helper untuk mengelola permission mart (`Manage Laporan Pos Multi Outlet`, `Manage Laporan Pos Kasir`, `POS Outlet`, dll.).
   - Menangani auto-assignment target user:
     - Koordinator Pondok Mart: Maulana Syarif (`maulanasyarif@gmail.com`), Maulani Izza Maharani (`maulani@gmail.com`), Ambar Eka Wati (`ambarsari@gmail.com`).
     - Outlet: Gus Maulana Rifqi (`gusrifqi@email.com`).
   - Logika pembatasan scope outlet pengguna (`isUserOutletRestricted` dan `getAllowedOutletIds`).
2. **`app/Http/Controllers/Admin/ReportMartCashflowController.php`** (Baru):
   - Controller modul Arus Kas Mart.
   - Otorisasi berbasis hak akses laporan mart / POS.
   - Paginasi aman Yajra DataTables (default length 25, fallback draw/start jika tidak dikirim client).
   - Filter sumber transaksi (`all`, `koperasi`, `outlet`), filter outlet spesifik, filter periode (`today`, `week`, `month`) dan rentang tanggal (`start_date`, `end_date`).
   - Strict scope enforcement: Pengguna outlet terkunci tidak dapat mengakses data transaksi atau ringkasan koperasi/outlet lain meski memanipulasi parameter query.
   - Perhitungan summary card metrics (Total Belanja Saldo, Tagihan Saldo Outlet, Pondok Mart / Koperasi, Rata-rata Belanja).
3. **`app/Exports/ReportMartCashflowExport.php`** (Baru):
   - Export Excel streaming menggunakan `FromGenerator` dengan pembacaan data secara chunked (`forPage(..., 500)`) agar aman dari memory exhaustion pada dataset transaksi skala besar.
4. **`database/migrations/2026_10_12_100000_add_arus_kas_mart_submenu_and_assign_roles.php`** (Baru):
   - Migration untuk mendaftarkan sub menu navigasi "Arus Kas Mart" di bawah menu navigasi "Laporan" (`/report-mart-cashflow`).
   - Menjalankan penugasan role awal untuk user target secara otomatis saat migrasi dieksekusi.
5. **`resources/views/admins/report-mart-cashflow/index.blade.php`** (Baru):
   - Antarmuka pengguna mengadopsi standar PakRT Native Vite: container radius `rounded-[24px]`, safe area horizontal `px-5`, elevasi bayangan halus, tipografi konsisten `<x-text.*>`, top-right action cluster, indikator status outlet & koperasi, serta detail item belanja expandable.
6. **`app/Models/User.php`** (Dimodifikasi):
   - Menambahkan method helper `isKoordinatorPondokMart()` dan menyertakan kata kunci `'pondok mart'` pada method `isKoordinatorCahayaMart()`.
7. **`resources/views/layouts/partials/sidebar.blade.php`** (Dimodifikasi):
   - Memastikan sub menu `report-mart-cashflow` tidak terblokir oleh filter role pada sidebar admin.
8. **`routes/web.php`** (Dimodifikasi):
   - Mendaftarkan route:
     - `GET /report-mart-cashflow` -> `ReportMartCashflowController@index` (name: `report-mart-cashflow.index`)
     - `GET /report-mart-cashflow/export` -> `ReportMartCashflowController@export` (name: `report-mart-cashflow.export`)
9. **`phpunit.xml`** (Dimodifikasi):
   - Menambahkan `<ini name="memory_limit" value="1024M"/>` untuk mendukung eksekusi test pada replika SQLite lokal dengan 152k record.
10. **`tests/Feature/ReportMartCashflowTest.php`** (Baru):
   - Test suite fungsional komprehensif (8 skenario pengujian, 65 assertions):
     - R1: Navigasi sub menu dan otorisasi akses pengguna.
     - R2: Validasi isolasi data hanya transaksi Saldo Santri (bukan UMUM).
     - R3: Pengujian filter sumber (Pondok Mart vs Outlet) dan filter rentang tanggal.
     - R4: Pengujian isolasi scope hak akses user outlet dan penugasan peran user target.
     - Export: Validasi response file unduhan Excel `.xlsx`.
     - Edge Cases: Penanganan hasil filter kosong dan boundary tanggal tanpa exception / error pembagian nol.

## 3. Hasil Pengujian Fungsional
Semua 8 unit/feature test berhasil dijalankan dengan status **PASS (100%)**:
```text
PASS  Tests\Feature\ReportMartCashflowTest
✓ r1 submenu arus kas mart exists in database and sidebar (12 assertions)
✓ r2 only saldo student transactions are returned (3 assertions)
✓ r3 source filter isolates koperasi and outlet (9 assertions)
✓ r3 date range filter works properly (3 assertions)
✓ r4 outlet user is strictly scoped to assigned outlet (6 assertions)
✓ r4 target users configuration and role assignment (18 assertions)
✓ excel export returns download response (3 assertions)
✓ edge case empty results and boundary filters (11 assertions)
Tests: 8 passed (65 assertions)
```
