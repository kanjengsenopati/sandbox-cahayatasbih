<?php

namespace App\Traits;

use App\Models\BillType;

trait CanonicalBillTypeTrait
{
    /**
     * Map raw bill type name to a unified canonical name.
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
                return 'LKS SMT 2';
            }
            if (str_contains($upper, 'SMT 1') || str_contains($upper, 'SEMESTER 1') || $upper === 'LKS SMT 1') {
                if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'LKS SMT 1 - MA';
                if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'LKS SMT 1 - SMP';
                return 'LKS SMT 1';
            }
            return 'LKS';
        }

        // 5. Infaq Kenaikan Kelas / Infaq Naik Kelas
        if (str_contains($upper, 'INFAQ')) {
            return 'INFAQ KENAIKAN KELAS';
        }

        // 6. Kalender
        if (str_contains($upper, 'KALENDER')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'KALENDER MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'KALENDER SMP';
            return 'KALENDER';
        }

        // 7. Piknik
        if (str_contains($upper, 'PIKNIK')) {
            if (str_contains($upper, 'MA') || $posBayarName === 'MADRASAH ALIYAH') return 'PIKNIK MA';
            if (str_contains($upper, 'SMP') || $posBayarName === 'SMP') return 'PIKNIK SMP';
            return 'PIKNIK';
        }

        // 8. Pendaftaran
        if (str_contains($upper, 'PENDAFTARAN')) {
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
     * Resolve a canonical or raw bill type input to all matching BillType IDs and Raw Names across academic years.
     */
    public function getMatchingBillTypeInfo($selectedInput): array
    {
        if (empty($selectedInput)) {
            return ['ids' => [], 'names' => []];
        }

        $allBillTypes = BillType::with('billItem')->get();
        $matchedIds = [];
        $matchedNames = [];

        $inputs = is_array($selectedInput) ? $selectedInput : [$selectedInput];

        foreach ($inputs as $input) {
            $inputCanonical = $this->getCanonicalBillTypeName((string)$input);

            foreach ($allBillTypes as $bt) {
                $pos = $bt->billItem->name ?? null;
                $canonical = $this->getCanonicalBillTypeName($bt->name, $pos);

                if (
                    $canonical === $inputCanonical ||
                    $bt->name === $input ||
                    $bt->id === $input ||
                    strcasecmp($canonical, (string)$input) === 0 ||
                    strcasecmp($bt->name, (string)$input) === 0
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
