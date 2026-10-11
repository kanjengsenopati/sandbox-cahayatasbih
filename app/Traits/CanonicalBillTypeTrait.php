<?php

namespace App\Traits;

use App\Models\BillItem;
use App\Models\BillType;
use App\Models\School;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait CanonicalBillTypeTrait
{
    /**
     * Daftar ID BillType legacy/anomali yang dikecualikan dari laporan.
     */
    public function getExcludedBillTypeIds(): array
    {
        return [
            '02dae620-fc2c-4bf2-9e13-c5c1950e4d48',
            '615a34af-be2d-45f2-9830-720fea341a0c',
            'f3a25c77-f8c0-4882-8286-571bc57bf87c',
            'ce389861-40ab-4523-9364-3458e9dfda1d',
        ];
    }

    /**
     * Resolusi daftar school_id yang diizinkan untuk admin yang sedang login.
     * - Jika $requestedSchoolId diisi: divalidasi terhadap cakupan UPT admin (Fail-Closed jika di luar wewenang).
     * - Jika $requestedSchoolId kosong ("Semua Lembaga"): mengembalikan seluruh UPT yang di-assign ke admin.
     */
    public function resolveScopedSchoolIds(?string $requestedSchoolId = null): array
    {
        $requestedSchoolId = (!empty($requestedSchoolId) && $requestedSchoolId !== 'null')
            ? trim($requestedSchoolId)
            : null;

        $admin = Auth::guard('web')->user() ?? Auth::user();
        $isSuperAdmin = !$admin
            || (method_exists($admin, 'isSuperAdmin') && $admin->isSuperAdmin())
            || (method_exists($admin, 'hasRole') && $admin->hasRole('Super Admin'));

        if ($isSuperAdmin) {
            if ($requestedSchoolId) {
                return [$requestedSchoolId];
            }

            return School::whereIn('type', [School::TYPE_PONDOK, School::TYPE_MA, School::TYPE_SMP])
                ->pluck('id')
                ->toArray();
        }

        $assignedSchoolIds = method_exists($admin, 'getSchoolIds')
            ? $admin->getSchoolIds()
            : ($admin->adminSchool ? $admin->adminSchool->pluck('school_id')->toArray() : []);

        if (empty($assignedSchoolIds)) {
            $assignedSchoolIds = School::whereIn('type', [School::TYPE_PONDOK, School::TYPE_MA, School::TYPE_SMP])
                ->pluck('id')
                ->toArray();
        }

        if ($requestedSchoolId) {
            return in_array($requestedSchoolId, $assignedSchoolIds, true)
                ? [$requestedSchoolId]
                : [];
        }

        return array_values(array_unique($assignedSchoolIds));
    }

    /**
     * Map School.type ke BillItem IDs (Pos Bayar/UPT) untuk database-driven filtering.
     */
    public function getBillItemIdsBySchoolType(?string $schoolType): Collection
    {
        $mapping = [
            School::TYPE_PONDOK => ['PONDOK'],
            School::TYPE_MA     => ['MADRASAH ALIYAH'],
            School::TYPE_SMP    => ['SMP'],
        ];

        $type = strtoupper(trim($schoolType ?? ''));
        $billItemNames = $mapping[$type] ?? [];

        if (empty($billItemNames)) {
            return collect();
        }

        return BillItem::whereIn('name', $billItemNames)->pluck('id');
    }

    /**
     * Map daftar school_id ke gabungan BillItem IDs (Pos Bayar/UPT).
     */
    public function getBillItemIdsBySchoolIds(array $schoolIds): Collection
    {
        if (empty($schoolIds)) {
            return collect();
        }

        $types = School::whereIn('id', $schoolIds)
            ->pluck('type')
            ->filter()
            ->unique();

        $billItemIds = collect();
        foreach ($types as $type) {
            $billItemIds = $billItemIds->merge($this->getBillItemIdsBySchoolType($type));
        }

        return $billItemIds->unique()->values();
    }

    /**
     * Map raw bill type name to a unified canonical name with strict UPT separation.
     */
    public function getCanonicalBillTypeName(string $rawName, ?string $posBayarName = null): string
    {
        $trimmed = trim($rawName);
        $upper = strtoupper($trimmed);

        // 1. Syahriah
        if (str_contains($upper, 'SYAHRIAH')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'SYAHRIAH MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'SYAHRIAH SMP';
            if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'SYAHRIAH PONDOK';
            return 'SYAHRIAH';
        }

        // 2. Zarkasi
        if (str_contains($upper, 'ZARKASI')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'ZARKASI MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'ZARKASI SMP';
            if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'ZARKASI PONDOK';
            return 'ZARKASI';
        }

        // 3. Aplikasi CT / Biaya Aplikasi
        if (str_contains($upper, 'APLIKASI')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'BIAYA APLIKASI CT - MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'BIAYA APLIKASI CT - SMP';
            if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'BIAYA APLIKASI CT - PONDOK';
            return 'BIAYA APLIKASI';
        }

        // 4. LKS Semester 1 & 2
        if (str_contains($upper, 'LKS')) {
            if (str_contains($upper, 'SMT 2') || str_contains($upper, 'SEMESTER 2')) {
                if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'LKS SMT 2 - MA';
                if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'LKS SMT 2 - SMP';
                if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'LKS SMT 2 - PONDOK';
                return 'LKS SMT 2';
            }
            if (str_contains($upper, 'SMT 1') || str_contains($upper, 'SEMESTER 1') || $upper === 'LKS SMT 1') {
                if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'LKS SMT 1 - MA';
                if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'LKS SMT 1 - SMP';
                if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'LKS SMT 1 - PONDOK';
                return 'LKS SMT 1';
            }
            return 'LKS';
        }

        // 5. Infaq Kenaikan Kelas / Infaq Naik Kelas (Separated per UPT to prevent cross-UPT collision)
        if (str_contains($upper, 'INFAQ')) {
            if (str_contains($upper, ' - MA') || str_ends_with($upper, ' MA') || $posBayarName === 'MADRASAH ALIYAH') {
                return 'INFAQ KENAIKAN KELAS - MA';
            }
            if (str_contains($upper, ' - SMP') || str_ends_with($upper, ' SMP') || $posBayarName === 'SMP') {
                return 'INFAQ KENAIKAN KELAS - SMP';
            }
            if (str_contains($upper, ' - PONDOK') || str_ends_with($upper, ' PONDOK') || $posBayarName === 'PONDOK') {
                return 'INFAQ KENAIKAN KELAS - PONDOK';
            }
            return 'INFAQ KENAIKAN KELAS';
        }

        // 6. Kalender
        if (str_contains($upper, 'KALENDER')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'KALENDER MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'KALENDER SMP';
            if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'KALENDER PONDOK';
            return 'KALENDER';
        }

        // 7. Piknik
        if (str_contains($upper, 'PIKNIK')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'PIKNIK MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'PIKNIK SMP';
            if (str_contains($upper, 'PONDOK') || $posBayarName === 'PONDOK') return 'PIKNIK PONDOK';
            return 'PIKNIK';
        }

        // 8. Pendaftaran (Separated per UPT to prevent cross-UPT collision)
        if (str_contains($upper, 'PENDAFTARAN')) {
            if (str_contains($upper, ' - MA') || str_ends_with($upper, ' MA') || $posBayarName === 'MADRASAH ALIYAH') {
                return 'PENDAFTARAN - MA';
            }
            if (str_contains($upper, ' - SMP') || str_ends_with($upper, ' SMP') || $posBayarName === 'SMP') {
                return 'PENDAFTARAN - SMP';
            }
            if (str_contains($upper, ' - PONDOK') || str_ends_with($upper, ' PONDOK') || $posBayarName === 'PONDOK') {
                return 'PENDAFTARAN - PONDOK';
            }
            return 'PENDAFTARAN';
        }

        // 9. Biaya Ujian / Administrasi Ujian
        if (str_contains($upper, 'UJIAN') || str_contains($upper, 'ADMINISTRASI')) {
            if (str_contains($upper, 'SMP AKHIR TAHUN')) return 'BIAYA UJIAN SMP AKHIR TAHUN';
            if (str_contains($upper, 'ADMINISTRASI PELAKSANAAN UJIAN')) return 'ADMINISTRASI PELAKSANAAN UJIAN';
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'BIAYA UJIAN MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'BIAYA UJIAN SMP';
            return $trimmed;
        }

        // 10. Kegiatan Akhir Tahun
        if (str_contains($upper, 'KEGIATAN AKHIR TAHUN')) {
            return 'BIAYA KEGIATAN AKHIR TAHUN';
        }

        return $trimmed;
    }

    /**
     * Resolve a canonical or raw bill type input to all matching BillType IDs and Raw Names
     * across academic years, strictly isolated within $allowedBillItemIds when provided.
     */
    public function getMatchingBillTypeInfo($selectedInput, $allowedBillItemIds = null): array
    {
        if (empty($selectedInput)) {
            return ['ids' => [], 'names' => []];
        }

        $query = BillType::with('billItem')->whereNotIn('id', $this->getExcludedBillTypeIds());

        if ($allowedBillItemIds !== null) {
            $idsArray = is_array($allowedBillItemIds)
                ? $allowedBillItemIds
                : ($allowedBillItemIds instanceof Collection ? $allowedBillItemIds->all() : (array) $allowedBillItemIds);

            // Fail-closed: jika scope Pos Bayar kosong, tidak boleh ada BillType yang lolos
            if (empty($idsArray)) {
                return ['ids' => [], 'names' => []];
            }

            $query->whereIn('bill_item_id', $idsArray);
        }

        $allBillTypes = $query->get();
        $matchedIds = [];
        $matchedNames = [];

        $inputs = is_array($selectedInput) ? $selectedInput : [$selectedInput];

        foreach ($inputs as $input) {
            $inputStr = trim((string) $input);
            if ($inputStr === '') {
                continue;
            }
            $inputCanonical = $this->getCanonicalBillTypeName($inputStr);

            foreach ($allBillTypes as $bt) {
                $pos = $bt->billItem->name ?? null;
                $canonical = $this->getCanonicalBillTypeName($bt->name, $pos);
                $canonicalWithoutPos = $this->getCanonicalBillTypeName($bt->name, null);

                // Match by exact UUID, exact UPT-aware canonical name, or generic canonical name (when input has no UPT suffix)
                $isGenericMatch = ($inputCanonical === $canonicalWithoutPos)
                    && in_array($inputCanonical, ['INFAQ KENAIKAN KELAS', 'PENDAFTARAN', 'SYAHRIAH', 'ZARKASI', 'BIAYA APLIKASI', 'LKS SMT 1', 'LKS SMT 2', 'KALENDER', 'PIKNIK'], true);

                if (
                    $bt->id === $inputStr ||
                    strcasecmp($canonical, $inputCanonical) === 0 ||
                    strcasecmp($canonical, $inputStr) === 0 ||
                    $isGenericMatch
                ) {
                    $matchedIds[] = $bt->id;
                    $matchedNames[] = $bt->name;
                }
            }
        }

        return [
            'ids' => array_values(array_unique($matchedIds)),
            'names' => array_values(array_unique($matchedNames)),
        ];
    }
}

