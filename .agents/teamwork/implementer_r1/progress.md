# Progress: Implementasi Sub Menu "Arus Kas Mart" pada Laporan

- [x] Sinkronisasi staging & pembuatan branch `feat/siswanto/laporan-arus-kas-mart`
- [x] Analisis codebase: struktur menu laporan, transaksi POS/mart, role/permission, scope outlet
- [x] Implementasi backend: Route, Controller / Service, Query riwayat belanja siswa berbasis saldo dengan filter Pondok Mart vs Outlet & scope hak akses
  - `App\Services\MartUserScopeService`: Pemetaan scope outlet pengguna, auto-assignment peran, resolusi hak akses multi-outlet vs single outlet.
  - `App\Http\Controllers\Admin\ReportMartCashflowController`: Controller Arus Kas Mart dengan dukungan filter sumber (Pondok Mart / Koperasi vs Outlet), filter outlet individual, filter rentang tanggal, paginasi aman DataTables Yajra, kalkulasi summary metrics (Total, Ditagihkan ke Bank Saldo, Belanja Pondok Mart), dan ekspor Excel.
  - `App\Exports\ReportMartCashflowExport`: Streaming generator chunked export Excel XLSX untuk mengatasi dataset ribuan baris tanpa memory exhaustion.
- [x] Implementasi frontend / blade / navigation: Sub menu "Arus Kas Mart" di menu Laporan, UI tabel riwayat belanja siswa, kontrol filter
  - Database navigation migration: Menambahkan sub menu "Arus Kas Mart" di bawah menu navigasi "Laporan" (`/report-mart-cashflow`).
  - View Blade: `resources/views/admins/report-mart-cashflow/index.blade.php` dibangun menggunakan standar desain PakRT Native Vite (`rounded-[24px]`, `<x-text.*>`, safe area `px-5`, badge status, indikator item transaksi collapsable, summary cards modern).
  - Sidebar: Memperbarui filter navigasi di `resources/views/layouts/partials/sidebar.blade.php` agar Koordinator Pondok Mart dapat mengakses modul secara mulus.
- [x] Konfigurasi / auto assign role & scope pengguna: Maulana Syarif, Rani, Ambar (Koordinator Pondok Mart), Gus Maulana Rifqi (Outlet)
  - Auto-assignment via migration & service: Maulana Syarif (`maulanasyarif@gmail.com`), Rani (`maulani@gmail.com`), Ambar (`ambarsari@gmail.com`) mendapatkan peran `KOORDINATOR PONDOK MART` tanpa pembatasan outlet (bisa memantau seluruh transaksi dan tagihan saldo outlet).
  - Gus Maulana Rifqi (`gusrifqi@email.com`) mendapatkan peran Outlet dengan scope terkunci hanya pada unit outlet yang ditugaskan (`OUTLET`).
- [x] Verifikasi & Testing
  - Feature test suite komprehensif di `tests/Feature/ReportMartCashflowTest.php` mencakup 8 pengujian otomatis (65 assertions): R1 (Menu & Hak Akses), R2 (Isolasi Saldo Santri vs Umum), R3 (Filter Koperasi vs Outlet & Date Range), R4 (Isolasi Hak Akses User Outlet & Konfigurasi User Sasaran), Excel Export, dan Edge Cases (Data Kosong & Boundary Date).
  - Seluruh 8 pengujian berhasil (PASS 100%).
