# Laporan Investigasi Bug: Loading Overlay "Persistent" di Halaman Pembayaran Siswa

Berdasarkan instruksi untuk melakukan investigasi forensik tanpa melakukan perubahan kode, berikut adalah hasil temuan dan *Root Cause Analysis* (RCA) terkait isu modal loading yang tidak tertutup (persisten) dan durasi load yang mencapai 15 detik lebih.

---

## 🔍 PHASE 1: TRACE SEMUA REQUEST

Pada halaman `admins.bill.index`, ketika pengguna memilih siswa dari dropdown `#student_id`, urutan *request* yang terjadi adalah sebagai berikut:

1. **AJAX Request (Select2):** `GET /admin/select2?data_type=STUDENT_BY_SCHOOL...`
   - **Trigger:** Saat pengguna mengetik/mencari siswa.
   - **Status:** Normal & cepat (~300ms).
2. **Synchronous Request (Form Submit):** `GET /admin/bill?student_id={id}`
   - **Trigger:** Event `change` pada `#student_id` yang memanggil `$(this).closest('form').submit()`.
   - **Status:** Menyebabkan *full page reload*. Memuat ulang seluruh DOM dan merender data siswa (`Data siswa akhirnya SUDAH berhasil ditampilkan`).
3. **AJAX Request (DataTables):** `GET /admin/bill`
   - **Trigger:** Di-trigger secara otomatis pada saat DOM baru selesai dimuat (sebelum perbaikan lazy-load) oleh inisialisasi `$('#table-transfer').DataTable()`.
   - **Status:** **SANGAT LAMBAT (±15 detik)**. Request ini memuat seluruh data transaksi tanpa filter spesifik dan mengalami bottleneck pada query Eloquent. Lebih parahnya, request ini mengunci *PHP Session* (Session Lock), sehingga request lain menjadi terblokir.

---

## 🔍 PHASE 2: TRACE LOADING STATE (ILUSI OPTIK)

Terdapat sebuah "Paradoks" pada laporan Anda: *"Data siswa akhirnya SUDAH berhasil ditampilkan, NAMUN modal 'Sedang memuat data...' tetap aktif"*. 

Jika halaman melakukan *full reload*, secara teknis DOM lama (termasuk modal SweetAlert) **pasti hancur/hilang**. Bagaimana mungkin modal tersebut tetap ada di halaman baru?

Investigasi menemukan bahwa **ada DUA overlay loading yang berbeda namun terlihat identik**:

1. **Loading Overlay 1 (SweetAlert):**
   - **Sumber:** `index.blade.php` baris 806 (`$('#filter-form').on('submit', ...)`)
   - **Teks:** `"Mohon Tunggu"`, `"Sedang memuat data..."`
   - **Siklus Hidup:** Muncul saat form disubmit, dan **HANCUR** saat halaman berpindah.
   
2. **Loading Overlay 2 (DataTables Processing):**
   - **Sumber:** `layouts/partials/script.blade.php` baris 10 (Global DataTables Defaults).
   - **HTML/CSS:** Di-inject dengan teks `<div class="fs-4 fw-bolder text-dark mb-2">Mohon Tunggu</div> <div class="fs-6 text-muted mb-4">Sedang memuat data...</div>`. 
   - **Styling:** Diberikan CSS `position: fixed`, `z-index: 10000`, `box-shadow`, dan background putih.
   - **Siklus Hidup:** Muncul di halaman **BARU** saat `#table-transfer` mulai melakukan AJAX fetching.

### 💡 The Root Cause (Akar Masalah)

Masalah ini adalah sebuah **Ilusi Optik UI**. 
Modal yang Anda lihat "tersangkut" (persisten) di layar **bukanlah** SweetAlert dari form submit. Melainkan indikator *processing* milik DataTables dari tab "Pembayaran Transfer".

**Kronologi Kejadian:**
1. Anda memilih siswa. Form submit memicu **SweetAlert (Overlay 1)**.
2. Browser melakukan *reload* halaman.
3. Halaman baru selesai dirender (Data siswa berhasil ditampilkan). SweetAlert (Overlay 1) telah lenyap.
4. Tepat pada detik yang sama di halaman baru, JavaScript menginisialisasi `#table-transfer`.
5. DataTables memicu AJAX request ke server dan memunculkan **DataTables Processing (Overlay 2)**.
6. Karena Overlay 2 memiliki teks dan desain CSS yang **sama persis** dengan Overlay 1 (berupa kotak putih di tengah layar dengan tulisan "Mohon Tunggu"), mata pengguna mengira bahwa modal pertama tidak pernah tertutup.
7. AJAX request DataTables memakan waktu **15 detik lebih** karena query lambat dan terblokir oleh *session lock*. Selama 15 detik inilah Overlay 2 berputar-putar tiada henti.

---

## ✅ KESIMPULAN

Bug ini sepenuhnya valid secara empiris. Akar masalahnya adalah:
1. **Performa Query:** AJAX endpoint untuk DataTables terlalu lambat.
2. **Eager Initialization:** DataTables `#table-transfer` diinisialisasi pada saat *page load*, padahal posisinya berada di tab yang tersembunyi (tidak aktif).
3. **Duplikasi UI Component:** Penggunaan desain *Processing Indicator* DataTables yang meniru persis desain SweetAlert memunculkan ilusi bug state pada frontend.

*(Catatan: Perbaikan kode berupa implementasi Lazy-load untuk menunda inisialisasi DataTables sampai tab diklik, serta pengoptimalan query & pelepasan session lock telah di-push sebelumnya pada branch `feat/siswanto/fix-slow-tagihan-loading` yang seharusnya telah menyelesaikan masalah ini).*
