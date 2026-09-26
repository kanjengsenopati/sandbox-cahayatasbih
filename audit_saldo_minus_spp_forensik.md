# Laporan Audit Forensik & Fakta: Akar Masalah Saldo Minus Santri

**Tanggal Audit**: 26 September 2026  
**Status**: Terverifikasi Empiris di Database Master (`cahayatasbihdb` & `aplikasidb`) & Tersimpan di CodeGraph Memory (`ID: c8284901-29dc-4dfc-a2a0-ede7dcd5d7f4`)

---

## 📌 Ringkasan Eksekutif (TL;DR)

1. **Bukan Kasir yang Meloloskan Jajan 1 Juta**:
   Kasir kantin melayani transaksi receh riil (roti, susu, snack Rp 2.000 – Rp 10.000). Tidak ada satu pun transaksi jajan bernilai Rp 1.000.000 di database.
2. **Penyedot Terbesar adalah Pemotongan Tagihan SPP/Pondok**:
   Sebanyak **388 dari 474 santri minus (82%)** saldonya terpotong oleh pembayaran tagihan resmi pesantren (`TransactionService::payWithBalance`) dengan total nominal **Rp 196.449.799 (Rp 196,4 Juta)**.
3. **Cacat Sistem Lama**:
   Sistem lama mengeksekusi autodebit/pembayaran tagihan via saldo tanpa mengecek kecukupan saldo (`where('saldo', '>=', $amount)` tidak ada). Tagihan SPP tercatat **LUNAS (PAID)**, tetapi saldo santri dipaksa **MINUS** hingga ratusan ribu sampai jutaan rupiah.
4. **Mengapa Orang Tua Tidak Komplain**:
   Sebanyak **413 dari 474 santri minus (87%)** fitur saldonya **dimatikan di HP Wali (`show_pwa_saldo = false`)** di level kelas/sekolah. Kartu saldo, mutasi saldo, dan menu topup **disembunyikan total** dari HP orang tua (hanya tampil Tagihan & Profil Santri).

---

## 📊 Statistik Faktual Database

* **Total Santri Bersaldo Minus**: **474 santri**
* **Total Nilai Defisit Kumulatif**: **Rp 30.249.151**
  * Santri Aktif (423 santri): Rp 27.722.231
  * Santri Lulus (25 santri): Rp 884.822
  * Santri Keluar / DO (14 santri): Rp 823.735
  * Santri Pindah (12 santri): Rp 818.363
* **Sebaran Nominal**:
  * Minus Ringan (< Rp 50.000): 295 santri (62% santri, rata-rata kasbon Rp 16.000)
  * Minus Sedang (Rp 50.000 s/d Rp 200.000): 147 santri
  * Minus Berat (> Rp 200.000): 32 santri (santri yang terkena autodebit SPP ratusan ribu/jutaan)

---

## 🔍 Bukti Sampel Log Pemotongan SPP ke Saldo Kosong

### 1. DAFFA IBNU HAFIDZ (NIS: 332125050) — Saldo Akhir: -Rp 737.354
* **Total Topup**: Rp 4.925.692 | **Total Potong SPP**: Rp 3.444.546 | **Total Jajan**: Rp 2.218.500
* **Log Bukti**:
  * `[2026-02-09 16:45:03]` Saldo Awal Rp 0 dipotong Tagihan Rp 150.000 $\rightarrow$ Saldo jadi **-Rp 150.000**
  * `[2026-02-09 16:45:25]` Selang 22 detik, dipotong lagi Tagihan Rp 500.000 $\rightarrow$ Saldo jadi **-Rp 650.000**

### 2. MUHAMMAD WAHAB SUHADA (NIS: 332125132) — Saldo Akhir: -Rp 236.452
* **Total Topup**: Rp 4.283.548 | **Total Potong SPP**: Rp 3.666.000 | **Total Jajan**: Rp 854.000
* **Log Bukti**:
  * `[2026-05-27 04:29:45]` Saldo sisa Rp 1.063.500 dipotong Tagihan Rp 1.000.000 $\rightarrow$ Sisa Rp 63.500
  * `[2026-05-27 04:29:47]` **Selang 2 detik**, dipotong lagi Tagihan Rp 1.000.000 $\rightarrow$ Saldo anjlok ke **-Rp 936.500**

### 3. PANDJI AURIGA KANZU (NIS: 330200542) — Saldo Akhir: -Rp 221.351
* **Total Topup**: Rp 4.921.149 | **Total Potong SPP**: Rp 3.740.000 | **Total Jajan**: Rp 1.402.500
* **Log Bukti**:
  * `[2026-03-08 07:29:16]` Saldo Rp 313.391 dipotong Tagihan Rp 500.000 $\rightarrow$ Saldo jadi **-Rp 186.609**
  * `[2026-04-11 01:39:23]` Saldo Rp 368.179 dipotong Tagihan Rp 620.000 $\rightarrow$ Saldo jadi **-Rp 251.821**
  * `[2026-04-11 01:39:52]` Dipotong lagi Tagihan Rp 500.000 $\rightarrow$ Saldo jadi **-Rp 751.821**

---

## 🛡️ Status Proteksi Sistem Saat Ini

1. **Backend Application Guard (Aktif ✅)**:
   Pada `OrderItemController.php` (Kasir PoS) dan `TransactionService.php` (`payWithBalance`), telah diterapkan atomic guard:
   ```php
   $affected = Student::where('id', $student->id)
       ->where('saldo', '>=', $pay_amount)
       ->decrement('saldo', $pay_amount);

   if ($affected === 0) {
       throw new \Exception('Maaf, Saldo Santri tidak mencukupi.');
   }
   ```
   Mustahil terjadi penambahan saldo minus baru pada transaksi saat ini dan masa depan.

2. **Frontend PoS Guard (Aktif ✅)**:
   Kasir langsung menampilkan modal peringatan saldo tidak cukup dan mendisinfeksi tombol bayar.
