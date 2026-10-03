<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SaldoMigrationBatch;
use App\Models\SaldoMigrationItem;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MigrationReceiverController extends Controller
{
    /**
     * Menerima payload snapshot saldo dari aplikasi lama dan menyimpannya sebagai batch pending.
     */
    public function receiveSaldo(Request $request)
    {
        $token = $request->input('migration_token');
        $validToken = config('services.migration.token', env('MIGRATION_SECRET_TOKEN', 'cahaya-tasbih-migration-secret'));

        if (empty($token) || $token !== $validToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token otentikasi migrasi tidak valid.',
            ], 401);
        }

        $studentsData = $request->input('students', []);
        if (empty($studentsData) || !is_array($studentsData)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daftar data santri kosong atau format tidak sesuai.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $studentsData, $token) {
                // Batalkan batch pending sebelumnya jika ada agar tidak menumpuk
                SaldoMigrationBatch::where('status', 'PENDING')->update([
                    'status' => 'SUPERSEDED',
                    'notes' => 'Digantikan oleh pengiriman batch baru pada ' . now()->toDateTimeString(),
                ]);

                $batch = SaldoMigrationBatch::create([
                    'migration_token' => $token,
                    'sent_by' => $request->input('sent_by', 'Aplikasi Lama'),
                    'sent_at' => $request->input('sent_at', now()),
                    'total_students' => count($studentsData),
                    'total_saldo' => (int) $request->input('total_saldo', 0),
                    'total_saving' => (int) $request->input('total_saving', 0),
                    'status' => 'PENDING',
                ]);

                // Ambil data siswa lokal untuk komparasi cepat
                $studentIds = array_filter(array_column($studentsData, 'id'));
                $studentNises = array_filter(array_column($studentsData, 'nis'));

                $localStudentsById = Student::whereIn('id', $studentIds)->get()->keyBy('id');
                $localStudentsByNis = Student::whereIn('nis', $studentNises)->get()->keyBy('nis');

                $itemsToInsert = [];
                $now = now()->toDateTimeString();

                foreach ($studentsData as $item) {
                    $stId = $item['id'] ?? null;
                    $stNis = $item['nis'] ?? null;

                    $localStudent = null;
                    if ($stId && $localStudentsById->has($stId)) {
                        $localStudent = $localStudentsById->get($stId);
                    } elseif ($stNis && $localStudentsByNis->has($stNis)) {
                        $localStudent = $localStudentsByNis->get($stNis);
                    }

                    $currentLocalSaldo = $localStudent ? (int) $localStudent->saldo : 0;
                    $oldSaldo = (int) ($item['saldo'] ?? 0);
                    $diffSaldo = $oldSaldo - $currentLocalSaldo;

                    $itemsToInsert[] = [
                        'id' => (string) Str::uuid(),
                        'batch_id' => $batch->id,
                        'student_id' => $localStudent ? $localStudent->id : ($stId ?: (string) Str::uuid()),
                        'nis' => $item['nis'] ?? null,
                        'nisn' => $item['nisn'] ?? null,
                        'name' => $item['name'] ?? ($localStudent ? $localStudent->name : 'Tanpa Nama'),
                        'classroom' => $item['classroom'] ?? ($localStudent?->classroom?->name ?? '-'),
                        'old_saldo' => $oldSaldo,
                        'old_saving' => (int) ($item['saving'] ?? 0),
                        'current_local_saldo' => $currentLocalSaldo,
                        'diff_saldo' => $diffSaldo,
                        'status' => 'PENDING',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach (array_chunk($itemsToInsert, 250) as $chunk) {
                    SaldoMigrationItem::insert($chunk);
                }

                Log::info("[MigrationReceiver] Berhasil menerima batch migrasi {$batch->id} berisi " . count($itemsToInsert) . " santri.");

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data migrasi berhasil diterima dan disimpan sebagai PENDING. Menunggu konfirmasi admin di Aplikasi Baru.',
                    'batch_id' => $batch->id,
                    'total_students' => count($itemsToInsert),
                    'total_saldo' => $batch->total_saldo,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error("[MigrationReceiver] Gagal memproses migrasi: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memproses data migrasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
