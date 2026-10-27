<?php

namespace App\Services;

use App\Models\PpdbRegistration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PsbSyncService
{
    /**
     * Uji koneksi ke database PSB terpisah
     */
    public function testConnection(): array
    {
        try {
            DB::connection('psb_mysql')->getPdo();
            $countTotal = DB::connection('psb_mysql')->table('ppdb_registrations')->count();
            $countAccepted = DB::connection('psb_mysql')->table('ppdb_registrations')
                ->where('status', 'ACCEPTED')
                ->count();
            $countUnsynced = DB::connection('psb_mysql')->table('ppdb_registrations')
                ->where('status', 'ACCEPTED')
                ->where('is_synced', false)
                ->count();

            return [
                'status' => true,
                'message' => 'Koneksi ke database PSB berhasil!',
                'total_registrations' => $countTotal,
                'accepted_count' => $countAccepted,
                'unsynced_count' => $countUnsynced,
            ];
        } catch (\Throwable $e) {
            Log::error('PsbSyncService connection error: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Gagal terhubung ke database PSB: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengambil daftar pendaftar yang diterima dan belum disinkronkan dari database PSB
     */
    public function getPendingAcceptedCandidates(int $limit = 50)
    {
        return DB::connection('psb_mysql')
            ->table('ppdb_registrations')
            ->where('status', 'ACCEPTED')
            ->where('is_synced', false)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Melakukan Pull Data dari PSB ke antrean ppdb_registrations di SIM
     */
    public function pullAcceptedCandidates(array $selectedIds = []): array
    {
        $connTest = $this->testConnection();
        if (!$connTest['status']) {
            return $connTest;
        }

        $query = DB::connection('psb_mysql')
            ->table('ppdb_registrations')
            ->where('status', 'ACCEPTED')
            ->where('is_synced', false);

        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }

        $candidates = $query->get();

        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($candidates as $cand) {
            try {
                DB::beginTransaction();

                // Deteksi status orang tua (Jamaah vs Umum)
                $isJamaah = (bool) ($cand->is_mdti_member ?? false);
                $mdtiBranch = $cand->mdti_branch ?? null;
                $mdtiGroup = $cand->mdti_group ?? null;
                $kta = $cand->kta ?? null;

                // Cek apakah sudah ada di SIM
                $existing = PpdbRegistration::where('registration_code', $cand->registration_code)
                    ->orWhere(function ($q) use ($cand) {
                        if (!empty($cand->nik)) {
                            $q->where('nik', $cand->nik);
                        }
                    })
                    ->first();

                $data = [
                    'source_psb_id' => (string) $cand->id,
                    'registration_code' => $cand->registration_code,
                    'status' => PpdbRegistration::STATUS_ACCEPTED,
                    'name' => $cand->name,
                    'nisn' => $cand->nisn,
                    'nik' => $cand->nik,
                    'birth_place' => $cand->birth_place,
                    'birth_date' => $cand->birth_date,
                    'gender' => $cand->gender,
                    'student_phone' => $cand->student_phone,
                    'student_email' => $cand->student_email ?? null,
                    'address_street' => $cand->address_street,
                    'rt' => $cand->rt ?? null,
                    'rw' => $cand->rw ?? null,
                    'village' => $cand->village,
                    'district' => $cand->district,
                    'city' => $cand->city,
                    'postal_code' => $cand->postal_code,
                    'kk_number' => $cand->kk_number,
                    'father_name' => $cand->father_name,
                    'father_nik' => $cand->father_nik,
                    'father_status' => $cand->father_status ?? 'HIDUP',
                    'father_job' => $cand->father_job,
                    'mother_name' => $cand->mother_name,
                    'mother_nik' => $cand->mother_nik,
                    'mother_status' => $cand->mother_status ?? 'HIDUP',
                    'mother_job' => $cand->mother_job,
                    'parent_phone' => $cand->parent_phone,
                    'is_mdti_member' => $isJamaah,
                    'mdti_branch' => $mdtiBranch,
                    'mdti_group' => $mdtiGroup,
                    'kta' => $kta,
                    'family_card_file' => $cand->family_card_file ?? null,
                    'origin_school' => $cand->origin_school ?? null,
                    'origin_school_address' => $cand->origin_school_address ?? null,
                    'medical_history' => $cand->medical_history ?? null,
                    'achievements' => $cand->achievements ?? null,
                    'admin_note' => 'Disinkronkan dari database PSB pada ' . now()->format('d/m/Y H:i'),
                ];

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    PpdbRegistration::create($data);
                    $imported++;
                }

                // Tandai di database PSB
                DB::connection('psb_mysql')
                    ->table('ppdb_registrations')
                    ->where('id', $cand->id)
                    ->update([
                        'is_synced' => true,
                        'synced_at' => now(),
                    ]);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error("Gagal sinkronisasi siswa ID {$cand->id} ({$cand->name}): " . $e->getMessage());
                $errors[] = "{$cand->name} ({$cand->registration_code}): " . $e->getMessage();
            }
        }

        return [
            'status' => true,
            'message' => "Proses sinkronisasi selesai. Berhasil diimpor: {$imported}, Diperbarui: {$updated}.",
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ];
    }

    /**
     * Konversi Calon Santri Diterima menjadi Santri Aktif (students) dan Akun Wali (users)
     */
    public function promoteToStudent(string $ppdbRegistrationId, ?string $schoolId = null, ?string $classroomId = null): array
    {
        try {
            DB::beginTransaction();

            $reg = PpdbRegistration::findOrFail($ppdbRegistrationId);

            // 1. Akun Wali Murid
            $parentPhone = preg_replace('/[^0-9]/', '', $reg->parent_phone);
            $parentName = $reg->father_name ?: ($reg->mother_name ?: 'Wali ' . $reg->name);

            $user = null;
            if (!empty($parentPhone)) {
                $user = User::where('phone', $parentPhone)->first();
            }

            if (!$user) {
                $user = User::create([
                    'id' => (string) Str::uuid(),
                    'name' => $parentName,
                    'phone' => $parentPhone,
                    'email' => $parentPhone ? $parentPhone . '@wali.cahayatasbih.or.id' : null,
                    'password' => bcrypt('12345678'), // default password
                    'role' => 'wali',
                    'is_active' => true,
                ]);
            }

            // 2. Data Santri Baru
            $student = Student::where('nisn', $reg->nisn)
                ->orWhere(function ($q) use ($reg) {
                    if (!empty($reg->nik)) {
                        $q->where('nik', $reg->nik);
                    }
                })
                ->first();

            if (!$student) {
                $student = Student::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'classroom_id' => $classroomId,
                    'name' => $reg->name,
                    'nisn' => $reg->nisn,
                    'nik' => $reg->nik,
                    'born_place' => $reg->birth_place,
                    'birth_date' => $reg->birth_date,
                    'gender' => $reg->gender,
                    'address' => trim("{$reg->address_street} RT {$reg->rt} RW {$reg->rw} {$reg->village} {$reg->district}"),
                    'city' => $reg->city,
                    'status' => Student::STATUS_ACTIVE,
                    'saldo' => 0,
                ]);
            }

            // Update status registrasi
            $reg->update([
                'user_id' => $user->id,
                'status' => 'ENROLLED',
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => "Santri {$student->name} berhasil diresmikan ke sistem aktif!",
                'student_id' => $student->id,
                'user_id' => $user->id,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal meresmikan santri PPDB ID {$ppdbRegistrationId}: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Gagal meresmikan santri: ' . $e->getMessage(),
            ];
        }
    }
}
