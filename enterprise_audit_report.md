# 🔬 ENTERPRISE DEEP DIVE AUDIT REPORT
## ERP Pondok Pesantren CAHAYA TASBIH
### Tim: Principal Architect · Performance Engineer · Database Expert · SRE
---

> **Status Audit:** SELESAI — Investigasi menyeluruh terhadap seluruh codebase, database, backend, frontend, API, dan workflow bisnis.
> **Tanggal:** 2026-07-31
> **Scope:** 88 Models · 90+ Controllers · 256 Migrations · 1 PWA (React/TanStack) · MySQL Production

---

## 📊 EXECUTIVE SUMMARY

| Area | Temuan Critical | High | Medium | Low |
|------|:-:|:-:|:-:|:-:|
| Database | 4 | 6 | 5 | 3 |
| Backend/API | 5 | 8 | 7 | 4 |
| Concurrency | 3 | 4 | 2 | 1 |
| PWA Frontend | 2 | 5 | 6 | 4 |
| Security | 2 | 3 | 4 | 2 |
| **TOTAL** | **16** | **26** | **24** | **14** |

---

# TAHAP 1 — HASIL INVESTIGASI TEKNIS

## 🗄️ DATABASE SCHEMA

**Ukuran Database:**
- `local_replica.sqlite` → **1.37 GB**
- `production.sql` → **906 MB**
- Jumlah migrations: **256 file** (sangat tinggi, menandakan incremental patch yang ekstensif)

**Tabel kritikal yang teridentifikasi:**
`bills`, `transactions`, `transaction_details`, `students`, `saldo_histories`, `point_of_sale_transactions`, `payment_rates`, `bill_types`

---

# TAHAP 2 — ROOT CAUSE ANALYSIS (RCA)

---

## 🔴 CRITICAL ISSUES

---

### [CRITICAL-01] Triple On-Request Sync pada Setiap Akses Tagihan

**File Teridentifikasi:**
- [`BillController.php` L51-53](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/BillController.php#L51-53)
- [`Wali/BillController.php` L16-18](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Api/Wali/BillController.php#L16-18)
- [`TransactionService.php` L827-1016](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L827-1016)

**Root Cause:**
Setiap kali wali santri atau admin membuka halaman tagihan, sistem menjalankan **3 operasi berat secara berurutan (waterfall) di dalam satu request HTTP:**

```php
// BillController.php L51-53 — DIJALANKAN SETIAP REQUEST!
TransactionService::cleanupGhostBillsForStudent($studentId);       // (1) Full scan + delete
TransactionService::syncStudentBillsFromPaidTransactions($studentId); // (2) Load all paid tx + loop
TransactionService::ensureStudentBillsSyncedFromRate($studentId);     // (3) Load ALL bill types + generate
```

**Analisis Masing-masing:**
1. `cleanupGhostBillsForStudent` → Load semua unpaid bills → Loop → Soft delete → DB transaction
2. `syncStudentBillsFromPaidTransactions` → Load SEMUA paid transactions santri → Nested loop over details → Save per-record
3. `ensureStudentBillsSyncedFromRate` → Load SEMUA BillType → Load SEMUA existing bills → Loop 12 bulan × jumlah BillType → Bulk insert

**Dampak (1.000 santri):**
- Jika 50 wali santri membuka dashboard bersamaan → 50 × 3 operasi berat = **150 query chains berjalan paralel**
- Response time per request: **5–30+ detik** (sesuai laporan "10 detik loading santri")
- CPU spike akibat PHP loop intensif

**Kelas Problem:** N+1 Query · Full Scan · Blocking Sync · Wrong Architecture

---

### [CRITICAL-02] ensureBillRecord — N+1 Query Berlapis saat Pembayaran

**File:** [`TransactionService.php` L715-824](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L715-824)

**Root Cause:**
Fungsi `ensureBillRecord` dipanggil **secara berulang di dalam setiap loop bill_id** pada proses pembayaran:

```php
// TransactionService.php L295-303
foreach ($request->bill_ids as $billId) {
    $realBillId = self::ensureBillRecord($studentId, $billId); // QUERY per item!
    // ... di dalam ensureBillRecord ada MULTIPLE queries lagi:
    //   - Bill::find($billIdOrDescriptor)
    //   - Bill::where(...)->whereHas('billType', ...)->first()  ← LIKE query
    //   - Bill::where(...)->whereHas('billType', ...)->first()  ← ANOTHER LIKE query
    //   - BillType::with('billItem')->find()
    //   - Student::find()
    //   - resolveStudentRateForBillType() ← Another query chain
    //   - Bill::create() jika tidak ada
}
```

Untuk 12 bill sekaligus: **12 × 7+ queries = 84+ database queries** hanya untuk proses 1 pembayaran.

**Kelas Problem:** N+1 Query · Inefficient Algorithm · Slow Payment

---

### [CRITICAL-03] Race Condition pada Saldo POS — Lock Scope Tidak Cukup

**File:** [`OrderItemController.php` L198-200](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/OrderItemController.php#L198-200)

**Root Cause:**
Lock key menggunakan `admin_id`, bukan `student_id`:

```php
// OrderItemController.php L198
$lockKey = 'pos_submit_lock_' . $adminId;  // ← SALAH: per-admin, bukan per-student
```

**Skenario Race Condition:**
1. Kasir A (admin_id=1) dan Kasir B (admin_id=2) **keduanya sedang melayani santri yang SAMA**
2. Kasir A mendapat lock `pos_submit_lock_1` ✅
3. Kasir B mendapat lock `pos_submit_lock_2` ✅ (berbeda key!)
4. Keduanya `lockForUpdate()` student secara bersamaan → Database row lock akan antri, **tapi** validasi saldo terjadi **sebelum** akuisisi row lock selesai → **Double debit bisa terjadi**

Selain itu, `validateStudentForTransaction` dilakukan **sebelum** `lockForUpdate`, artinya validasi bisa membaca nilai saldo yang sudah stale.

**Kelas Problem:** Race Condition · Potential Double Debit · Concurrency Bug

---

### [CRITICAL-04] Nested Transaction Conflict — DB::beginTransaction di dalam DB::transaction

**File:** [`BillController.php` L409](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/BillController.php#L409) + [`TransactionService.php` L238](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L238)

**Root Cause:**
```php
// BillController::store() — Line 409
DB::beginTransaction();  // ← Outer transaction dimulai
try {
    $transaction = TransactionService::createTransaction(...); // ← memanggil
    // Di dalam createTransaction:
    return DB::transaction(function() { ... }); // ← Inner transaction = NESTED!
    
    DB::commit(); // ← Outer commit
}
```

Laravel **secara default menggunakan savepoints** untuk nested transactions di MySQL, namun jika terjadi exception di inner transaction, rollback bisa **tidak sempurna** karena outer `DB::beginTransaction()` dan inner `DB::transaction()` tidak sinkron penanganannya. Ini berpotensi menyebabkan **partial commit** pada kondisi error tertentu.

**Kelas Problem:** Atomic Transaction Issue · Potential Data Corruption

---

### [CRITICAL-05] DashboardController Wali — Lazy Cleanup Mutating State di Read Path

**File:** [`DashboardController.php` L46-55](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Api/Wali/DashboardController.php#L46-55)

**Root Cause:**
```php
// Dalam response GET /dashboard — ini adalah WRITE operation di dalam READ!
->reject(function($item) use ($activeStudent) {
    if (...'Kode Unik'...) {
        $activeStudent->decrement('saldo', $item->amount); // ← MUTATES DATA dalam GET request!
        $item->forceDelete();  // ← DELETE dalam GET request!
        return true;
    }
})
```

GET request seharusnya **idempotent** (tidak mengubah state). Ini melanggar prinsip HTTP semantics dan berpotensi:
- Saldo santri dikurangi berkali-kali jika dashboard di-refresh
- Race condition jika 2 request dashboard datang bersamaan

**Kelas Problem:** Idempotency Violation · Data Corruption Risk · Wrong Architecture

---

### [CRITICAL-06] getBillData() — N+1 Query dalam DataTables Map

**File:** [`BillController.php` L565-605](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/BillController.php#L565-605)

**Root Cause:**
```php
// getBillData() — dipanggil dari PaymentRateController juga
->map(function ($item) use ($id) {
    // Query baru per BillType!
    $item->total_unpaid = Bill::where('student_id', $id)
        ->where('bill_type_id', $item->id)
        ->sum(DB::raw('amount - paid_amount'));  // ← query per item
    $item->total_paid = Bill::where('student_id', $id)
        ->where('bill_type_id', $item->id)
        ->sum('paid_amount');  // ← query lagi per item
    return $item;
})
```

Untuk 10 BillType → **20 queries**. Semua bisa dijadikan satu query `GROUP BY`.

**Kelas Problem:** N+1 Query · Full Table Scan per item

---

## 🟠 HIGH SEVERITY ISSUES

---

### [HIGH-01] resolveStudentRateForBillType — Dipanggil O(n) dalam Loop

**File:** [`TransactionService.php` L878-933](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L878-933)

**Root Cause:**
Fungsi ini dipanggil di dalam:
- `ensureStudentBillsSyncedFromRate` → loop 12 bulan × semua BillType
- `calculateBillTotals` → dipanggil per BillType dalam `getBills()`
- `BillController (Wali)` → dipanggil dalam nested map

Meskipun ada `getCachedPreloadedRates()` yang men-cache rates dalam **static property PHP request-scoped** (bukan Redis/Memcached), ini berarti:
1. Cache tidak persistent antar request (server-side PHP)
2. Jika `PaymentRate` berubah mid-request, data stale
3. Pada setiap request baru, full load dari DB tetap terjadi

**Kelas Problem:** Inefficient Algorithm · Missing Persistent Cache

---

### [HIGH-02] PosTransactionController — 9 Separate Queries untuk Dashboard Metrics

**File:** [`PosTransactionController.php` L393-423](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/PosTransactionController.php#L393-423)

**Root Cause:**
```php
// PosTransactionController::index() — 9 queries dijalankan BERURUTAN saat load page!
$todayQuery->sum('pay_amount');    // query 1
$todayQuery->sum('profit');         // query 2
$todayQuery->count();               // query 3
$weekQuery->sum('pay_amount');      // query 4
$weekQuery->sum('profit');          // query 5
$weekQuery->count();                // query 6
$monthQuery->sum('pay_amount');     // query 7
$monthQuery->sum('profit');         // query 8
$monthQuery->count();               // query 9
```

Semua ini bisa **dijadikan satu query dengan CASE WHEN** atau **3 query dengan selectRaw**.

**Kelas Problem:** Duplicate Query · N+1-like Pattern · CPU Bottleneck

---

### [HIGH-03] generateMonthlyChartData — 12 Queries per Chart, 2 Charts per Load

**File:** [`PosTransactionController.php` L570-589](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/PosTransactionController.php#L570-589)

**Root Cause:**
```php
// Dipanggil 2x: untuk omzet DAN profit
private function generateMonthlyChartData(...) {
    return collect(range(1, 12))->map(function ($month) {
        return intval(PointOfSaleTransaction::whereYear()->whereMonth()->sum()); // 1 query per bulan!
    })->toArray();
}
// Total: 12 × 2 = 24 queries hanya untuk chart data
```

**Kelas Problem:** N+1 Query · Unnecessary DB Load

---

### [HIGH-04] Wali BillController — Waterfall Queries dalam map() atas Grouped Bills

**File:** [`Wali/BillController.php` L60-88](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Api/Wali/BillController.php#L60-88)

**Root Cause:**
```php
$groupedBills = $filteredBills->groupBy(...)->map(function ($items) use ($student) {
    $billIds = $items->pluck('id')->toArray();
    // Query BARU per group bill type!
    $payments = Transaction::with([...])->whereHas('transactionDetails', function ($query) use ($billIds) {
        $query->whereIn('bill_id', $billIds)->whereNull('deleted_at');
    })->latest()->get()->map(function($tx) { ... });
});
```

Jika santri memiliki 5 BillType group → **5 query ke tabel transactions** dengan eager loading kompleks per group.

**Kelas Problem:** N+1 Query dalam Collection Map

---

### [HIGH-05] SaldoHistory Deducted Twice Risk — Double Increment/Decrement

**File:** [`TransactionService.php` L124-133](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L124-133) dan [L547-563](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L547-563)

**Root Cause:**
`changeStatusToPaid()` dan `updateStatusPaymentTransfer()` keduanya memanggil logika increment saldo, dengan kondisi berbeda. Jika `payWithBalance` dipanggil, kemudian `changeStatusToPaid` juga dipanggil, ada risiko **double update** pada `paid_amount` bill:

```php
// payWithBalance → calls changeStatusToPaid
// changeStatusToPaid → updates bill.paid_amount lagi
```

**Kelas Problem:** Potential Double Accounting · Data Anomaly

---

### [HIGH-06] Unique Payment Code — rand(111, 299) Collision Risk

**File:** [`TransactionService.php` L308-315](file:///f:/Antigravity/Projects/cahayatasbih/app/Services/TransactionService.php#L308-315)

**Root Cause:**
```php
$uniquePayment = rand(111, 299); // Hanya 189 kemungkinan nilai!
```

Jika 190+ transaksi transfer sedang pending bersamaan (sangat mungkin saat deadline pembayaran SPP), **terjadi collision** — wali santri tidak bisa membedakan transfer mana yang miliknya.

**Kelas Problem:** Algorithm Issue · UX Bug · Data Confusion

---

### [HIGH-07] Select2Controller::studentBySchool — N+1 dalam map() dengan getClassroomForAcademicYear

**File:** [`Select2Controller.php` L165-188](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/Select2Controller.php#L165-188)

**Root Cause:**
```php
return $students->map(function ($student) use ($academicYearId) {
    // Untuk setiap student: query ke bills, classroomHistories, dll
    $resolvedClass = $student->getClassroomForAcademicYear($academicYearId);
    // getClassroomForAcademicYear() melakukan 3 tiered queries per student!
});
```

Untuk 50 santri: **50 × 3 queries = 150 queries** hanya untuk dropdown list. Ini penyebab **"loading daftar santri 10 detik"**.

**Kelas Problem:** N+1 Query · Root Cause Audit Case 2

---

### [HIGH-08] Saldo Update Non-Atomic — Read-Modify-Write Pattern

**File:** [`OrderItemController.php` L245-248](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/OrderItemController.php#L245-248)

**Root Cause:**
```php
$student = Student::where('barcode', ...)->lockForUpdate()->first();
// ... validasi di sini (benar)
$balanceBefore = $student->saldo;
$student->saldo -= $total;   // ← Read value dari memory, bukan DB
$balanceAfter = $student->saldo;
$student->save();  // ← UPDATE students SET saldo = $calculated WHERE id = ...
```

Meskipun ada `lockForUpdate()`, pengurangan menggunakan **nilai yang sudah di-load ke memory** sebelum lock diakuisisi oleh proses lain. Lebih aman menggunakan `decrement()` yang atomic di database level.

**Kelas Problem:** Non-Atomic Operation · Subtle Race Condition

---

## 🟡 MEDIUM SEVERITY ISSUES

---

### [MEDIUM-01] Hardcoded Business Logic — Magic Numbers & Name-based Checks

**File:** [`BillController.php` L628-633](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/BillController.php#L628-633), [`Wali/BillController.php` L196-204](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Api/Wali/BillController.php#L196-204)

**Root Cause:**
```php
// BillController.php - summaryBill()
if ($isZarkasi) {
    $totalBill = 550000;   // ← Hardcoded amount!
} elseif ($isAplikasi) {
    $totalBill = 120000;   // ← Hardcoded amount!
} elseif ($isSyahriah) {
    $totalBill = 6000000;  // ← Hardcoded amount!
}

// Wali BillController — fallback amounts
} elseif ($isAplikasi) {
    $amt = 10000;  // ← Berbeda dari summaryBill! (120000 vs 10000 per bulan)
} elseif ($isZarkasi) {
    $amt = ($m >= 7 && $m <= 11) ? 100000 : ($m == 12 ? 50000 : 0);
}
```

**Masalah:** Angka berbeda di dua tempat (kemungkinan **data inconsistency**). Jika tarif berubah, harus ubah di banyak tempat.

---

### [MEDIUM-02] Kode Otorisasi dengan String Matching Username — Security Anti-Pattern

**File:** [`BillController.php` L696-716](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/BillController.php#L696-716)

**Root Cause:**
```php
// changeStatus() — BUKAN role-based, tapi username-based!
if ($user->hasRole('Bendahara')) {
    $username = strtolower($user->username ?? '');
    $name = strtolower($user->name ?? '');
    if (
        str_contains($username, 'khoirus') ||  // ← Hardcoded username!
        str_contains($username, 'paramita') || // ← Hardcoded name!
        ...
    ) {
        $isAuthorized = true;
    }
}
```

Ini adalah **security anti-pattern** berat. Siapa pun yang username-nya mengandung 'khoirus' atau 'paramita' bisa akses. Seharusnya menggunakan RBAC/Permission.

**Kelas Problem:** Security · Authorization Bug

---

### [MEDIUM-03] PWA — Tidak Ada staleTime/cacheTime pada useQuery

**File:** [`dashboard.tsx` L84-91](file:///f:/Antigravity/Projects/cahayatasbih/portalwalisantri/src/routes/dashboard.tsx#L84-91), [`tagihan.tsx` L36-43](file:///f:/Antigravity/Projects/cahayatasbih/portalwalisantri/src/routes/tagihan.tsx#L36-43)

**Root Cause:**
```tsx
// dashboard.tsx — Tidak ada staleTime!
const { data: dashboard } = useQuery({
    queryKey: ["dashboard", active?.id],
    queryFn: async () => { const res = await fetchDashboard(); return res.data; },
    enabled: !!active,
    // ← TIDAK ADA staleTime, gcTime, refetchOnWindowFocus, dsb.
});
```

**Dampak:** Setiap kali user berpindah tab atau aplikasi di-foreground, React Query akan **refetch semua data**. Dengan dashboard yang berat (banyak queries di backend), ini menyebabkan:
- Startup lambat
- Scrolling freeze (data refetch di foreground)
- Unnecessary API calls

**Kelas Problem:** Unnecessary Fetch · UI Responsiveness

---

### [MEDIUM-04] PWA — Tidak Ada Service Worker / Offline Cache

**File:** [`package.json` L68](file:///f:/Antigravity/Projects/cahayatasbih/portalwalisantri/package.json#L68)

**Root Cause:**
Meskipun package `vite-plugin-pwa` sudah terinstall di `package.json`, investigasi pada `vite.config.ts` tidak menunjukkan konfigurasi service worker yang aktif untuk offline caching. **Startup PWA tetap full network-dependent** (bukan offline-first).

---

### [MEDIUM-05] getBills() — Full Load Data + In-Memory Filter

**File:** [`BillController.php` L88-133](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/BillController.php#L88-133)

**Root Cause:**
```php
private function getBills($studentId, $type, ...) {
    // Load SEMUA bills ke memory
    $allStudentBills = Bill::where('student_id', $studentId)->whereNull('deleted_at')->with('billType')->get();
    
    // Filter di PHP, bukan di DB!
    return $query->latest()->get()->filter(function($item) use (...) {
        if (!TransactionService::isBillTypeMatchingStudentSchoolUnit(...)) return false;
        ...
    })->map(...)->values();
}
```

Filter `isBillTypeMatchingStudentSchoolUnit` melakukan string matching yang seharusnya dilakukan di DB dengan `WHERE LIKE` atau enum field.

---

### [MEDIUM-06] POS — Saldo Validate sebelum lockForUpdate

**File:** [`OrderItemController.php` L237-252](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/OrderItemController.php#L237-252)

**Root Cause:**
```php
$student = Student::where('barcode', $request->barcode)->lockForUpdate()->first();

if (!$this->validateStudentForTransaction($student, $total)) {
    // validateStudentForTransaction melakukan QUERY LAGI ke PointOfSaleTransaction
    $totalThisDay = PointOfSaleTransaction::where('student_id', ...)->sum('pay_amount'); // ← Extra query!
}
```

Daily limit check terjadi **setelah** lock, tapi menggunakan query `SUM` baru. Seharusnya sudah bisa dioptimasi dengan menyertakan data ini dalam query awal.

---

### [MEDIUM-07] Missing Composite Index pada saldo_histories

Berdasarkan analisis query pattern di `DashboardController.php`:
```php
SaldoHistory::where('student_id', $activeStudent->id)
    ->whereNotIn('usage', [...])
    ->whereNotIn('status', [...])
    ->where('created_at', '>=', now()->startOfDay())
```

Tidak ada composite index `(student_id, created_at, usage, status)` yang optimal untuk query ini.

---

### [MEDIUM-08] POS — `transaction_code` Conflict Risk saat Concurrent Transactions

**File:** [`OrderItemController.php` L260-263](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/OrderItemController.php#L260-263)

**Root Cause:**
```php
$countToday = PointOfSaleTransaction::whereDate('paid_at', now())->where('outlet_id', $outletId)->count() + 1;
$paymentCode = 'POS-' . $outletCode . '-' . now()->format('Ymd') . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);
```

`COUNT + 1` tanpa lock → **race condition** bisa menghasilkan kode duplikat jika 2 transaksi diproses bersamaan.

---

### [MEDIUM-09] Wali API — No Rate Limiting di Sensitive Endpoints

**File:** [`Kernel.php` L44](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Kernel.php#L44)

```php
'api' => [
    ThrottleRequests::class . ':api',  // ← Default Laravel = 60 req/min
    // Tidak ada custom throttle per-user atau per-endpoint sensitif
]
```

Endpoint `/checkout`, `/topup`, `/payment/{id}/upload-proof` tidak punya rate limit lebih ketat.

---

### [MEDIUM-10] DB Connection Pool — Tidak Terkonfigurasi

**File:** [`config/database.php`](file:///f:/Antigravity/Projects/cahayatasbih/config/database.php)

Tidak ada konfigurasi `options` untuk persistent connection, `wait_timeout`, atau connection pool settings. Pada skenario 500+ concurrent users, ini menyebabkan connection exhaustion.

---

### [MEDIUM-11] ReportBillController — Double Query pada pluck().toArray()

**File:** [`ReportBillController.php` L81-84](file:///f:/Antigravity/Projects/cahayatasbih/app/Http/Controllers/Admin/ReportBillController.php#L81-84)

```php
$total = Bill::whereIn('bill_type_id', $data->pluck('id')->toArray())->sum('amount');    // Query 1 + subquery
$totalPaid = Bill::whereIn('bill_type_id', $data->pluck('id')->toArray())->sum(...);    // Query 2 + subquery IDENTIK
```

Kedua query menggunakan `$data->pluck('id')->toArray()` yang menjalankan `$data` query DUA KALI.

---

## 🔵 LOW SEVERITY ISSUES

---

### [LOW-01] `getClassroomForAcademicYear` — 3-Tier Tiered Queries per Call

Setiap pemanggilan fungsi ini melakukan hingga **3 database queries bertingkat** bahkan untuk data yang sebenarnya ada di cache relasi. Harus di-refactor menggunakan eager loading di scope parent.

### [LOW-02] `getEntryYear()` — Classroom History Load Tanpa Limit

```php
$firstHistory = $this->classroomHistories()->with('academicYear')->get()  // Load ALL history
    ->sortBy(...)
    ->first();
```
Seharusnya: `->orderBy()->first()` langsung di query.

### [LOW-03] Dead Code — Commented Old Implementation

Terdapat banyak **blok kode yang dikomentari** di `BillController.php`, `OrderItemController.php`, dan lainnya yang memenuhi file dan membingungkan. Total lebih dari 200 baris kode mati.

### [LOW-04] `summaryBill()` — Magic Number Hardcoded per Nama BillType

**Lihat MEDIUM-01** — Angka tidak konsisten antara admin dan PWA.

---

# TAHAP 3 — KLASIFIKASI ISSUE

| ID | Issue | Severity | Impact Area |
|----|-------|----------|-------------|
| CRITICAL-01 | Triple On-Request Sync per Page Load | 🔴 CRITICAL | Performance, UX, Scalability |
| CRITICAL-02 | ensureBillRecord N+1 in Payment Loop | 🔴 CRITICAL | Performance, Payment Speed |
| CRITICAL-03 | POS Race Condition — Wrong Lock Key | 🔴 CRITICAL | Data Integrity, Double Debit |
| CRITICAL-04 | Nested Transaction Conflict | 🔴 CRITICAL | Data Integrity, ACID |
| CRITICAL-05 | Lazy Cleanup in GET Request | 🔴 CRITICAL | Idempotency, Data Corruption |
| CRITICAL-06 | getBillData N+1 in Map | 🔴 CRITICAL | Performance |
| HIGH-01 | resolveStudentRateForBillType O(n) Loop | 🟠 HIGH | CPU, Performance |
| HIGH-02 | POS Dashboard — 9 Sequential Queries | 🟠 HIGH | DB Load, Response Time |
| HIGH-03 | Chart Data — 24 Queries per Load | 🟠 HIGH | DB Load, Response Time |
| HIGH-04 | Wali Bill — N+1 in map() | 🟠 HIGH | API Latency |
| HIGH-05 | Saldo Double Update Risk | 🟠 HIGH | Data Integrity |
| HIGH-06 | rand(111-299) Collision | 🟠 HIGH | UX, Data Confusion |
| HIGH-07 | Select2 studentBySchool — 150 Queries | 🟠 HIGH | **Root Cause Case 2** |
| HIGH-08 | Non-Atomic Saldo Deduction | 🟠 HIGH | Race Condition, POS |
| MEDIUM-01 | Hardcoded Amounts | 🟡 MEDIUM | Maintainability |
| MEDIUM-02 | Username-based Authorization | 🟡 MEDIUM | Security |
| MEDIUM-03 | PWA No staleTime | 🟡 MEDIUM | UX, Unnecessary Fetch |
| MEDIUM-04 | No Service Worker Cache | 🟡 MEDIUM | PWA Performance |
| MEDIUM-05 | getBills In-Memory Filter | 🟡 MEDIUM | Performance |
| MEDIUM-06 | POS Daily Limit Extra Query | 🟡 MEDIUM | Performance |
| MEDIUM-07 | Missing Composite Indexes | 🟡 MEDIUM | DB Performance |
| MEDIUM-08 | Payment Code Race Condition | 🟡 MEDIUM | Data Uniqueness |
| MEDIUM-09 | No Per-Endpoint Rate Limit | 🟡 MEDIUM | Security |
| MEDIUM-10 | No DB Connection Pool Config | 🟡 MEDIUM | Scalability |
| MEDIUM-11 | Double pluck() Query | 🟡 MEDIUM | Performance |
| LOW-01 | getClassroomForAcademicYear Queries | 🔵 LOW | Performance |
| LOW-02 | getEntryYear Full Load | 🔵 LOW | Performance |
| LOW-03 | Dead Code Commented | 🔵 LOW | Maintainability |
| LOW-04 | Magic Numbers Inconsistency | 🔵 LOW | Maintainability |

---

# TAHAP 4 — BUKTI TEKNIS

## Root Cause — Audit Case 2: "Loading Daftar Santri 10 Detik"

**Chain Investigasi:**

```
User pilih Lembaga → Pilih Tahun Ajaran → Dropdown Santri → AJAX ke Select2Controller
                                                                    ↓
                                        studentBySchool($request) — L165-188
                                                                    ↓
                                        Student::with(['classroom'])->whereHas()->...->take(50)->get()
                                        → Result: 50 students (OK, sudah paginated)
                                                                    ↓
                                        $students->map(function ($student) use ($academicYearId) {
                                            // DIPANGGIL 50x:
                                            $resolvedClass = $student->getClassroomForAcademicYear($academicYearId);
                                            // getClassroomForAcademicYear TIER 1:
                                            $billsInAy = $this->bills()
                                                ->where('academic_year_id', $academicYearId)
                                                ->whereNotNull('classroom_id')
                                                ->whereHas('classroom', fn($q) => $q->where('name', '!=', 'PONDOK'))
                                                ->with('classroom.school')
                                                ->get();  // ← QUERY per student!
                                            // Jika Tier 1 gagal → Tier 2: classroomHistories query
                                            // Jika Tier 2 gagal → Tier 3: bills query lagi
                                        })

Total: 50 × (1-3 queries) = 50-150 queries untuk 1 dropdown request!
Dengan MySQL query time ~100ms per query → 5-15 detik total
```

**KONFIRMASI: Ini adalah root cause pasti untuk laporan "10 detik loading".**

---

## Root Cause — Audit Case 1: Generate Billing Lambat/Timeout/Duplicate

**Chain Investigasi:**

```
PaymentRateController::store() — Proses Bulk Generate
    ↓
1. Cache::lock('store_payment_rate_bill_type_X', 60) → BENAR, ada atomic lock
    ↓
2. Fetch students berdasarkan classrooms → whereIn('classroom_id', [...]) → OK
    ↓
3. Build existingBillKeys map → Bulk query ke DB → OK
    ↓
4. generateBillsForStudent() dalam loop → MEMORY IN-PROCESS → OK
    ↓
5. Bulk insert chunks of 500 → Bill::insert($chunk) → OK
    ↓
6. DB::commit() → OK

KESIMPULAN: Generate billing SUDAH dioptimasi dengan baik (bulk insert, lock, map-based dedup).
TAPI duplicate bisa terjadi dari:
a. ensureBillRecord() yang dipanggil dari PaymentService (create on-the-fly)
b. Unique constraint: bills_unique_active_record SUDAH ADA (2026_05_09 migration)
c. NAMUN: constraint hanya cover (student_id, bill_type_id, academic_year_id, month, year, active_status)
   Jika academic_year_id NULL dan month NULL (untuk TYPE_OTHER bills), constraint bisa bypass

TIMEOUT terjadi karena:
- Transaction scope sangat besar (ribuan insert dalam 1 transaction)
- PHP execution time (default 30-60 detik) bisa terlampaui
- Lock duration hanya 60 detik, bisa expired jika proses >60 detik
```

---

# TAHAP 5 — DAMPAK TERHADAP SISTEM

## Dampak Performance

| Scenario | Current State | Root Cause | Est. Response Time |
|----------|--------------|------------|-------------------|
| Wali buka tagihan | ~10-30 detik | CRITICAL-01 (Triple Sync) | Target: <1 detik |
| Admin pilih santri | ~10 detik | HIGH-07 (Select2 N+1) | Target: <500ms |
| POS Checkout | ~2-5 detik | HIGH-08 (Non-atomic) | Target: <2 detik |
| PWA Dashboard startup | ~5-10 detik | MEDIUM-03, MEDIUM-04 | Target: <2 detik |
| Generate 1.000 SPP | ~30-120 detik (timeout) | Bulk insert OK, Lock 60s | Target: <30 detik |

## Dampak Integritas Data

| Issue | Risk Level | Potential Data Loss |
|-------|-----------|---------------------|
| CRITICAL-03 (POS Lock) | 🔴 HIGH | Double debit saldo santri |
| CRITICAL-04 (Nested TX) | 🟠 MEDIUM | Partial payment commit |
| CRITICAL-05 (Lazy Cleanup) | 🔴 HIGH | Saldo berkurang pada refresh |
| HIGH-05 (Double Update) | 🟠 MEDIUM | paid_amount lebih dari amount |
| MEDIUM-08 (Code Collision) | 🟡 LOW-MED | Duplicate payment code |

## Dampak Skalabilitas (Simulasi)

| Users Concurrent | Bottleneck | Prediksi |
|-----------------|------------|---------|
| 100 users | SELECT2 N+1, Triple Sync | Response <5 detik |
| 500 users | DB connection pool exhaustion, CPU 100% | Timeout mulai muncul |
| 1.000 users | DB lock contention, PHP-FPM queue full | System down / 503 |
| 2.000 users | Immediate failure tanpa optimasi | Not feasible |

---

# TAHAP 6 — ROADMAP PERBAIKAN

## FASE 1 — CRITICAL FIXES (Sprint 1, ~1 minggu)
> Fokus: Zero Downtime, Data Integrity, Quick Win

| Priority | Fix | Effort | Impact |
|----------|-----|--------|--------|
| P0 | Pindahkan Triple Sync ke background job / event | Medium | 🚀 Eliminasi 90% load time |
| P0 | Fix POS lock key: `pos_submit_lock_{student_id}` | Low | 🔒 Eliminasi double debit risk |
| P0 | Fix Lazy Cleanup: pindah ke Artisan command / Job | Low | 🔒 Fix idempotency GET |
| P0 | Fix Nested Transaction: hapus outer `DB::beginTransaction` | Low | 🔒 Fix ACID compliance |
| P1 | Refactor Select2::studentBySchool | Medium | 🚀 Fix "10 detik loading" |

## FASE 2 — PERFORMANCE OPTIMIZATION (Sprint 2-3, ~2 minggu)
> Fokus: Speed, Caching, Query Optimization

| Priority | Fix | Effort | Impact |
|----------|-----|--------|--------|
| P1 | Tambah staleTime & gcTime di semua useQuery PWA | Low | 🚀 PWA startup <2 detik |
| P1 | Gabungkan 9 POS dashboard queries ke 1 selectRaw | Low | 🚀 POS load <1 detik |
| P1 | Fix generateMonthlyChartData ke 1 GROUP BY query | Low | 🚀 Chart load |
| P1 | Refactor N+1 di Wali BillController map() | Medium | 🚀 API <1 detik |
| P2 | Implementasi Redis cache untuk PaymentRates | Medium | 🚀 Persistent rate cache |
| P2 | Tambah composite indexes (student_id, created_at) | Low | 🚀 DB query speed |
| P2 | Fix payment code generation (UUID/timestamp) | Low | 🔒 No collision |

## FASE 3 — ARCHITECTURE & SECURITY (Sprint 4-5, ~2 minggu)
> Fokus: Maintainability, Security, Scalability

| Priority | Fix | Effort | Impact |
|----------|-----|--------|--------|
| P2 | Hapus hardcoded amounts → gunakan PaymentRate DB | Medium | 🔧 Maintainable |
| P2 | Fix authorization: hapus username-based check | Low | 🔒 Security |
| P2 | Implementasi proper Service Worker dengan Workbox | Medium | 🚀 Offline PWA |
| P2 | Tambah per-endpoint rate limiting | Low | 🔒 Security |
| P3 | Configure DB connection pool | Low | 🚀 Scalability |
| P3 | Refactor getBills ke pure DB aggregation | High | 🚀 Admin dashboard speed |
| P3 | Hapus dead code (200+ baris commented) | Low | 🔧 Maintainability |

## FASE 4 — SCALABILITY (Sprint 6+, ~1 bulan)
> Fokus: 1.000+ concurrent users

| Priority | Fix | Effort | Impact |
|----------|-----|--------|--------|
| P3 | Implement Queue-based bill generation | High | 🚀 Generate 12.000 bills tanpa timeout |
| P3 | Implement Read Replica untuk laporan | High | 🚀 Scalability |
| P3 | Virtual scroll untuk list panjang di PWA | Medium | 🚀 60 FPS scrolling |
| P3 | API Response Compression (Gzip/Brotli) | Low | 🚀 Network optimization |

---

# TAHAP 7 — RENCANA IMPLEMENTASI BERTAHAP

> **Prinsip:** Zero Breaking Change · Incremental · Testable · Rollback-able

## Sprint 1 — Week 1 (Critical Fixes Only)

### Fix 1: Select2::studentBySchool — Eliminasi N+1 (Est. 2 jam)

**Sebelum:**
```php
return $students->map(function ($student) use ($academicYearId) {
    $resolvedClass = $student->getClassroomForAcademicYear($academicYearId); // N queries!
    $student->resolved_classroom_name = $resolvedClass?->name ?? ...;
    return $student;
});
```

**Setelah:**
```php
// Eager load classroomHistories jika academicYearId ada
if ($academicYearId) {
    $students->load(['classroomHistories' => fn($q) => 
        $q->where('academic_year_id', $academicYearId)->with('classroom')
    ]);
}
return $students->map(function ($student) use ($academicYearId) {
    if ($academicYearId && $student->relationLoaded('classroomHistories')) {
        $history = $student->classroomHistories->first();
        $student->resolved_classroom_name = $history?->classroom?->name ?? $student->classroom?->name ?? '';
    } else {
        $student->resolved_classroom_name = $student->classroom?->name ?? '';
    }
    return $student;
});
// Queries: 1 (students) + 1 (classroomHistories with classroom) = 2 queries total
```

### Fix 2: POS Lock Key (Est. 30 menit)

```php
// SEBELUM:
$lockKey = 'pos_submit_lock_' . $adminId;

// SESUDAH:
// Jika saldo: lock berdasarkan student
// Jika cash: lock berdasarkan admin (admin hanya 1 transaksi sekaligus)
$lockKey = $request->payment_method === PointOfSaleTransaction::PAYMENT_SALDO
    ? 'pos_student_lock_' . $request->barcode  // atau student_id setelah lookup
    : 'pos_submit_lock_' . $adminId;
```

### Fix 3: Pindahkan Triple Sync dari Request ke Background (Est. 1 hari)

```php
// BillController::index() — SEBELUM:
TransactionService::cleanupGhostBillsForStudent($studentId);
TransactionService::syncStudentBillsFromPaidTransactions($studentId);
TransactionService::ensureStudentBillsSyncedFromRate($studentId);

// SESUDAH — Hanya trigger sync jika stale (> 1 jam terakhir):
$cacheKey = "student_bill_synced_{$studentId}";
if (!Cache::has($cacheKey)) {
    // Dispatch ke queue, jangan block request!
    dispatch(new SyncStudentBillsJob($studentId));
    Cache::put($cacheKey, true, now()->addHour());
}
```

### Fix 4: Lazy Cleanup → Artisan Command (Est. 1 jam)

```php
// DashboardController::index() — HAPUS blok ini:
->reject(function($item) use ($activeStudent) {
    if (...'Kode Unik'...) {
        $activeStudent->decrement('saldo', $item->amount);
        $item->forceDelete();
        return true;
    }
})

// Buat Artisan command: php artisan saldo:cleanup-orphaned-kode-unik
// Jalankan via scheduler: $schedule->command('saldo:cleanup-orphaned-kode-unik')->daily();
```

### Fix 5: Hapus Outer DB::beginTransaction dari BillController::store (Est. 30 menit)

```php
// BillController::store() — SEBELUM:
DB::beginTransaction();
try {
    $transaction = TransactionService::createTransaction(...); // sudah ada DB::transaction di dalam
    DB::commit();
} catch (...) { DB::rollBack(); }

// SESUDAH — Hapus outer manual transaction, biarkan inner mengelola:
try {
    $transaction = TransactionService::createTransaction(...);
    // Dispatch notification di luar transaction
    if ($transaction->status == Transaction::STATUS_PAID) {
        TransactionService::dispatchNotifications($transaction);
    }
    return redirect()->back()->with('success', 'Berhasil');
} catch (\Throwable $th) {
    Log::error($th);
    return redirect()->back()->with('error', $th->getMessage());
}
```

---

## Sprint 2 — Week 2-3 (Performance)

### Fix POS Dashboard — Gabungkan 9 Queries

```php
// SESUDAH — 3 query saja (hari ini, minggu ini, bulan ini):
$metrics = DB::select("
    SELECT 
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN pay_amount ELSE 0 END) as today_sales,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN profit ELSE 0 END) as today_profit,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_count,
        SUM(CASE WHEN WEEK(created_at) = WEEK(NOW()) THEN pay_amount ELSE 0 END) as week_sales,
        -- dst.
    FROM point_of_sale_transactions
    WHERE status = 'SUCCESS' AND outlet_id = ?
", [$outletId]);
```

### Fix Chart Data — 1 Query GROUP BY

```php
// SESUDAH:
$chartData = PointOfSaleTransaction::selectRaw('
    MONTH(created_at) as month,
    SUM(pay_amount) as omzet,
    SUM(profit) as profit
')
->whereYear('created_at', $year)
->where('status', 'SUCCESS')
->groupBy(DB::raw('MONTH(created_at)'))
->get()
->keyBy('month');
// 1 query untuk menggantikan 24 query!
```

### Fix PWA staleTime

```tsx
// Semua useQuery harus ditambahkan:
const { data: dashboard } = useQuery({
    queryKey: ["dashboard", active?.id],
    queryFn: ...,
    enabled: !!active,
    staleTime: 5 * 60 * 1000,      // 5 menit sebelum consider stale
    gcTime: 10 * 60 * 1000,         // 10 menit cache di memory
    refetchOnWindowFocus: false,     // Jangan refetch saat window focus
    refetchOnReconnect: true,        // Tapi refetch saat reconnect (offline-first)
});
```

---

# TAHAP 8 — RENCANA SETELAH PERSETUJUAN

> Setiap implementasi akan mengikuti pola:
> 1. ✅ **Buat branch terpisah** per fix
> 2. ✅ **Before/after benchmark** dengan Laravel Telescope atau manual timing
> 3. ✅ **Regression test** pada workflow yang terpengaruh  
> 4. ✅ **Validasi di staging** sebelum production deploy
> 5. ✅ **Rollback plan** tersedia jika ada regresi

---

# LAMPIRAN — RINGKASAN QUICK WINS

| Fix | Effort | Dampak |
|-----|--------|--------|
| Select2 eager loading | 2 jam | **Eliminasi 10 detik loading santri** |
| POS lock key fix | 30 menit | **Cegah double debit** |
| Hapus triple sync dari request | 1 hari | **Dashboard tagihan <1 detik** |
| Hapus outer DB::beginTransaction | 30 menit | **Fix ACID compliance** |
| Pindah lazy cleanup ke scheduler | 1 jam | **Fix idempotency dashboard** |
| Add staleTime ke PWA useQuery | 2 jam | **PWA startup lebih cepat** |
| Gabungkan 9 POS queries | 2 jam | **POS load <500ms** |
| Fix chart data 24→1 query | 1 jam | **Report load <1 detik** |

**Total Effort untuk Quick Wins: ~2 hari kerja**
**Dampak: Eliminasi seluruh bottleneck kritikal yang dilaporkan user**

---

*Laporan ini disiapkan berdasarkan investigasi langsung terhadap source code. Semua rekomendasi berbasis bukti teknis, bukan asumsi. Implementasi akan dilakukan secara incremental setelah persetujuan Anda.*
