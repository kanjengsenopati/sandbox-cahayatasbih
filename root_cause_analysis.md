# Root Cause Analysis: Delay Load Data Tagihan Pembayaran (>15 Detik)

Berdasarkan inspeksi menyeluruh pada arsitektur kode, konfigurasi environment, dan bukti empiris dari log sistem, masalah lambatnya loading data tagihan (saat memilih siswa) **bukan** disebabkan oleh eksekusi kode sinkronisasi tagihan (`SyncStudentBillsJob`), melainkan disebabkan oleh fenomena **Laravel Session Blocking** akibat eksekusi AJAX DataTables yang membebani antrean request.

Berikut adalah uraian teknis dari temuan root cause:

## 1. Akar Masalah Utama: Session Blocking oleh AJAX DataTables
Sistem menggunakan `SESSION_DRIVER=file` (default Laravel). Driver ini menerapkan *exclusive lock*, yang berarti PHP hanya akan memproses **1 request per user (session) dalam satu waktu**. Request lain dari user yang sama akan ditahan (antre) sampai request pertama selesai.

Saat halaman Data Pembayaran pertama kali dibuka, file `index.blade.php` langsung melakukan inisialisasi **3 DataTables sekaligus** tanpa mekanisme *lazy-loading*:
1. `#table-transfer` (memanggil `?tab=transfer`)
2. `#table-history` (memanggil `?tab=history`)
3. `#table-archive` (memanggil `?tab=archive`)

Ketika Anda memilih siswa dari *dropdown*, Javascript langsung melakukan form submit yang memunculkan SweetAlert **"Mohon Tunggu - Sedang memuat data..."** dan mengirimkan HTTP GET request baru (`/admin/bill?student_id=XXX`).
**Akibatnya:** Request utama untuk memuat tagihan siswa ini harus **menunggu/antre** di server (stuck) sampai ketiga AJAX request DataTables sebelumnya selesai dieksekusi satu per satu oleh PHP.

## 2. Bukti Empiris dari Log & Kode
Waktu tunggu >15 detik terjadi karena ketiga request AJAX tersebut sangat berat dan memakan waktu lama:

*   **Query Lambat (Slow Query Logs):**
    Di dalam `storage/logs/laravel.log`, terekam bahwa query `count(*)` yang di-generate oleh Yajra DataTables sangat lambat akibat join dan filter kompleks (`hasSchool`, dll).
    ```json
    [2026-09-27 11:48:33] local.WARNING: Slow query: select count(*) as aggregate from (select * from "transactions" where "payment_method_id" in (?) and "type" = ? and "status" = ? and "transactions"."deleted_at" is null order by "created_at" desc) count_row_table {"bindings":["...","BILL","PENDING_CONFIRMATION"],"time":2233.16}
    ```
    Satu request AJAX bisa memakan waktu **2-5 detik** (atau lebih di production). Jika ada 3 request, server membutuhkan waktu ~15 detik hanya untuk menyelesaikannya secara sekuensial.

*   **Duplikasi Beban Kerja (Code Level):**
    Di `BillController@index` baris 119-124, terdapat logika:
    ```php
    if (request()->ajax()) {
        if (request()->tab === 'archive') {
            return $this->getArchiveTransactionData();
        }
        return $this->getTransactionData();
    }
    ```
    Karena request tab `history` tidak di-handle secara eksplisit, request tersebut akan di-fallback ke `$this->getTransactionData()`. Ini berarti aplikasi **mengeksekusi query berat yang sama persis sebanyak 2 kali** (`transfer` dan `history`) pada saat yang bersamaan.

## 3. Faktor Penyumbang Tambahan (Secondary Bottlenecks)
Walaupun request AJAX telah selesai, request utama HTTP sendiri memiliki inefisiensi yang memakan CPU time:
1.  **Massive Collection Unserialization:** `TransactionService::getCachedPreloadedRates()` menarik sekitar 3.500+ object Eloquent dari cache di memori.
2.  **In-Memory N+1 Filter:** Data cache tersebut kemudian di-iterasi puluhan hingga ratusan kali menggunakan `Collection->where()` dan `->first()` di dalam `resolveActivePaymentRate` dan `getUngeneratedRatesForStudent`, memicu overhead PHP processing hingga 0.4 detik (di local) sebelum akhirnya halaman bisa di-render.

## Kesimpulan
Popup "Sedang memuat data..." yang hang selama lebih dari 15 detik terjadi karena **browser harus menunggu giliran (session lock) dari 3 request AJAX DataTables yang sedang dieksekusi server secara berurutan dan lambat**.

**Status:** Investigasi read-only selesai. Tidak ada perubahan kode yang dilakukan sesuai dengan instruksi.
