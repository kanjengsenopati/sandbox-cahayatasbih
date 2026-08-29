# Product Requirements Document (PRD): Sistem Sinkronisasi & Isolasi Tarif Pembayaran

## 1. Latar Belakang & Tujuan
Modul "Data Tarif Pembayaran" digunakan oleh Admin untuk mengkonfigurasi dan menyebarkan (generate/sync) tagihan kepada siswa berdasarkan kelompok-kelompok tertentu (misal: kelas, status jamaah, status alumni).

**Tujuan PRD ini:** Memastikan logika sinkronisasi tagihan bersifat **terisolasi, persisten, dan terproteksi ketat**. Kesalahan dalam distribusi tagihan (seperti satu tarif menimpa siswa di luar kelompoknya) tidak boleh terjadi.

## 2. Aturan Bisnis Inti (Core Business Rules)

### 2.1. Strict Isolation (Isolasi Ketat)
Setiap baris Tarif Pembayaran (Payment Rate) memiliki parameter filter yang mengikat. Parameter ini adalah identitas mutlak dari tarif tersebut.
Parameter filter meliputi:
1. **Kelas (Classroom)**
2. **Jenis Kelamin (Gender)**
3. **Status Jamaah (Jamaah / Non Jamaah / Mukimin)**
4. **Status Alumni (Alumni SMP / Non Alumni)**
5. **Sub Status Siswa (Kategori khusus seperti PPTQ)**

**Rule Mutlak:** 
Ketika tombol "Generate" pada sebuah baris tarif diklik, sistem **HANYA BOLEH** menyentuh, membuat, atau memperbarui tagihan milik siswa yang **memenuhi 100% kriteria filter** dari baris tarif tersebut. 

### 2.2. Proteksi Lintas Status (Cross-Status Protection)
- Jika Admin men-generate tarif **Rp 1.000.000 untuk Alumni SMP**, maka tagihan ini **TIDAK BOLEH** menimpa tagihan milik siswa **Non Alumni**.
- Sebaliknya, jika Admin men-generate tarif **Rp 5.000.000 untuk Non Alumni**, sistem **TIDAK BOLEH** menyentuh atau menghapus tagihan milik siswa Alumni SMP.
- Isolasi ini menjamin bahwa setiap grup siswa hanya akan terpengaruh oleh aksi Generate pada baris tarif yang didesain khusus untuk mereka.

### 2.3. Prioritas Update Tagihan (Idempotency & Force Update)
- **Tagihan Belum Dibayar (UNPAID):** Jika terdapat perubahan nominal pada master tarif, aksi Generate akan menimpa nominal tagihan `UNPAID` agar sinkron dengan master tarif terbaru.
- **Tagihan Cicilan (PARTIAL):** Jika siswa sudah menyicil namun ada perubahan tarif, status tagihan tetap disesuaikan dengan proporsi pembayaran terhadap nominal master yang baru.
- **Tagihan Lunas (PAID):** Jika nominal master turun dan berada di bawah nominal yang sudah dibayar, tagihan akan otomatis terkunci di status Lunas.

## 3. Implementasi Teknis (Technical Implementation)

### 3.1. Filter Berlapis pada Backend Command
Proses distribusi tagihan dieksekusi oleh Background Job / Artisan Command `bills:sync-rate`.
Pencarian target siswa (`getStudentsForRate()`) menggunakan klausa *query builder* yang ketat:
```php
// Logika Isolasi Status Alumni
if ($paymentRate->alumni_status) {
    $alumniStatuses = array_map('trim', explode(',', $paymentRate->alumni_status));
    $query->where(function ($q) use ($alumniStatuses) {
        $alumniSubquery = \Illuminate\Support\Facades\DB::table('student_classroom_histories')
            ->join('classrooms', 'classrooms.id', '=', 'student_classroom_histories.classroom_id')
            ->join('schools', 'schools.id', '=', 'classrooms.school_id')
            ->where('schools.name', 'like', '%SMP%')
            ->whereNull('student_classroom_histories.deleted_at')
            ->select('student_id');

        if (in_array('ALUMNI_SMP_MA', $alumniStatuses)) {
            $q->orWhere(function ($qAlumni) use ($alumniSubquery) {
                $qAlumni->whereHas('classroom.school', function ($sq) {
                    $sq->where('name', 'like', '%MA%');
                })->whereIn('students.id', clone $alumniSubquery);
            });
        }

        if (in_array('NON_ALUMNI', $alumniStatuses)) {
            $q->orWhere(function ($qNonAlumni) use ($alumniSubquery) {
                $qNonAlumni->whereDoesntHave('classroom.school', function ($sq) {
                    $sq->where('name', 'like', '%MA%');
                })->orWhereNotIn('students.id', clone $alumniSubquery);
            });
        }
    });
}
```

### 3.2. Race Condition Protection
Jika Admin menekan tombol Generate berulang kali atau menekan beberapa tombol secara bersamaan untuk tipe tagihan yang sama, sistem menggunakan **Atomic Lock (Cache Lock)** selama 300 detik untuk mencegah duplikasi penyisipan data tagihan ke dalam database.

## 4. Referensi Komponen Terkait
- **UI Component:** `resources/views/admins/bill-type/show.blade.php`
- **Controller:** `app/Http/Controllers/Admin/PaymentRateController.php` (`generate` & `generateStudent`)
- **Background Sync Service:** `app/Console/Commands/SyncPaymentRateBills.php` (`getStudentsForRate`)

---
*Dokumen ini merupakan sumber kebenaran (Single Source of Truth) untuk logika sinkronisasi tarif pembayaran dan harus dijadikan acuan bagi developer di masa mendatang untuk mencegah regresi logika bisnis.*
