<?php

namespace App\Traits;

use Carbon\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogActivityTrait
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('system')
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                return self::buildHumanDescriptionFromModel($this, $eventName);
            });
    }

    public function tapActivity($activity, string $eventName): void
    {
        $userAgent = request()->userAgent() ?? 'Unknown';
        $device = $this->parseDevice($userAgent);

        $activity->properties = $activity->properties->merge([
            'device' => $device,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Map technical table names to human-friendly [object_label, module_name].
     */
    public static function getModuleMetadata(string $table): array
    {
        $map = [
            'admins' => ['data petugas', 'Manajemen Petugas & Akses'],
            'users' => ['akun Wali Santri', 'Manajemen Wali Santri'],
            'students' => ['data santri', 'Manajemen Kesantrian'],
            'transactions' => ['transaksi pembayaran', 'Transaksi & Keuangan'],
            'transaction_details' => ['rincian transaksi pembayaran', 'Transaksi & Keuangan'],
            'transaction_proofs' => ['bukti pembayaran', 'Verifikasi Pembayaran'],
            'bills' => ['data tagihan santri', 'Tagihan & SPP'],
            'bill_types' => ['kategori jenis tagihan', 'Master Keuangan & Tagihan'],
            'bill_items' => ['komponen rincian tagihan', 'Master Keuangan & Tagihan'],
            'payment_rates' => ['tarif pembayaran tagihan', 'Pengaturan Tarif Tagihan'],
            'payment_methods' => ['metode pembayaran', 'Pengaturan Kanal Pembayaran'],
            'banks' => ['rekening bank pembayaran', 'Pengaturan Kanal Pembayaran'],
            'saldo_banks' => ['rekening top-up saldo', 'Dompet Digital Santri'],
            'saving_banks' => ['rekening tabungan santri', 'Tabungan Santri'],
            'saldo_histories' => ['mutasi saldo dompet santri', 'Dompet Digital Santri'],
            'saving_histories' => ['mutasi tabungan santri', 'Tabungan Santri'],
            'point_of_sale_transactions' => ['transaksi belanja kasir', 'Kasir Koperasi & Outlet (POS)'],
            'items' => ['data produk/barang', 'Katalog Barang Outlet'],
            'category_items' => ['kategori barang', 'Katalog Barang Outlet'],
            'stock_histories' => ['riwayat stok barang', 'Manajemen Stok Outlet'],
            'outlets' => ['data unit usaha (outlet)', 'Manajemen Outlet & Koperasi'],
            'outlet_handovers' => ['setoran serah terima kasir', 'Manajemen Outlet & Koperasi'],
            'karyawans' => ['data karyawan unit usaha', 'Manajemen SDM & Outlet'],
            'officers' => ['data pengurus pondok', 'Kepegawaian & SDM'],
            'payrolls' => ['data penggajian (payroll)', 'Payroll & Penggajian'],
            'working_shifts' => ['jadwal jam kerja (shift)', 'Presensi & SDM'],
            'biometric_devices' => ['perangkat mesin presensi', 'Presensi Biometrik'],
            'cash_flows' => ['pencatatan arus kas', 'Kas & Arus Keuangan'],
            'cash_flow_categories' => ['kategori arus kas', 'Kas & Arus Keuangan'],
            'schools' => ['data unit pendidikan (UPT)', 'Master Akademik'],
            'classrooms' => ['data kelas/rombel', 'Master Akademik'],
            'academic_years' => ['data tahun ajaran', 'Master Akademik'],
            'semesters' => ['data semester akademik', 'Master Akademik'],
            'studies' => ['data mata pelajaran', 'Kurikulum & Akademik'],
            'study_grades' => ['nilai akademik santri', 'Penilaian Akademik'],
            'tahfidzs' => ['capaian hafalan tahfidz', 'Tahfidz Al-Qur\'an'],
            'student_achievements' => ['catatan prestasi santri', 'Pengasuhan & Kesantrian'],
            'student_counseling_scores' => ['poin konseling santri', 'Pengasuhan & Kesantrian'],
            'student_permits' => ['data perizinan santri', 'Perizinan & Kesantrian'],
            'asramas' => ['data kamar/asrama santri', 'Manajemen Asrama'],
            'schedules' => ['agenda & jadwal kegiatan', 'Kalender Akademik & Pondok'],
            'informations' => ['pengumuman & berita', 'Pusat Informasi Pondok'],
            'information_categories' => ['kategori informasi', 'Pusat Informasi Pondok'],
            'ppdbs' => ['data pendaftaran PSB', 'Penerimaan Santri Baru (PSB)'],
            'ppdb_registrations' => ['berkas pendaftaran santri baru', 'Penerimaan Santri Baru (PSB)'],
            'registrations' => ['pendaftaran santri baru', 'Penerimaan Santri Baru (PSB)'],
            'ppdb_waves' => ['gelombang pendaftaran PSB', 'Penerimaan Santri Baru (PSB)'],
            'ppdb_tracks' => ['jalur seleksi PSB', 'Penerimaan Santri Baru (PSB)'],
            'ppdb_types' => ['kategori seleksi PSB', 'Penerimaan Santri Baru (PSB)'],
            'roles' => ['peran & otorisasi pengguna', 'Manajemen Hak Akses'],
            'permissions' => ['izin akses fitur', 'Manajemen Hak Akses'],
            'application_settings' => ['pengaturan sistem aplikasi', 'Pengaturan Aplikasi'],
            'app_informations' => ['informasi profil aplikasi', 'Pengaturan Aplikasi'],
            'contacts' => ['kontak layanan pondok', 'Pengaturan Aplikasi'],
            'application_menus' => ['konfigurasi menu aplikasi', 'Pengaturan Navigasi'],
            'menu_navigations' => ['menu navigasi utama', 'Pengaturan Navigasi'],
            'sub_menu_navigations' => ['sub-menu navigasi', 'Pengaturan Navigasi'],
            'student_card_settings' => ['template kartu santri', 'Manajemen Kartu Santri'],
            'student_card_reports' => ['laporan kendala kartu santri', 'Manajemen Kartu Santri'],
            'unit_transfer_configs' => ['konfigurasi mutasi unit', 'Mutasi & Akademik'],
        ];

        return $map[strtolower($table)] ?? ['data operasional', ' Operasional Backoffice'];
    }

    /**
     * Check whether a string is a raw UUID or meaningless technical placeholder.
     */
    protected static function isTechnicalIdentifier(?string $value): bool
    {
        if ($value === null) {
            return true;
        }
        $trimmed = trim($value);
        if ($trimmed === '' || in_array(strtolower($trimmed), ['data', '-', 'null', 'system'], true)) {
            return true;
        }
        // UUID v4 / standard 36-char hex UUID pattern
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $trimmed);
    }

    /**
     * Build a human-friendly description when saving a new activity log on a Model instance.
     */
    public static function buildHumanDescriptionFromModel($model, string $eventName): string
    {
        $table = method_exists($model, 'getTable') ? $model->getTable() : '';
        $dirty = method_exists($model, 'getDirty') ? $model->getDirty() : [];
        $attrs = method_exists($model, 'getAttributes') ? $model->getAttributes() : [];

        // Detect login update on Admin
        if ($table === 'admins' && $eventName === 'updated' && array_key_exists('last_login_at', $dirty) && count($dirty) <= 2) {
            return 'Masuk (login) ke sistem melalui fitur Autentikasi Backoffice';
        }

        // Detect login update on User (Wali Santri)
        if ($table === 'users' && $eventName === 'updated' && array_key_exists('last_login', $dirty) && count($dirty) <= 2) {
            $name = $model->name ?? null;
            $label = !self::isTechnicalIdentifier($name) ? " ({$name})" : '';
            return "Akses masuk (login) Wali Santri{$label} pada fitur Portal Wali Santri (PWA)";
        }

        $rawLabel = $model->name ?? $model->title ?? $model->payment_code ?? $model->nis ?? null;
        return self::composeHumanNarrative($table, $eventName, $rawLabel, $attrs, $dirty, false);
    }

    /**
     * Transform any Spatie Activity model (both historical technical logs and new logs)
     * into a human-friendly narrative with semantic badge metadata.
     *
     * @param  mixed  $activity
     * @return array{description: string, badge_label: string, badge_class: string, module: string}
     */
    public static function humanizeActivity($activity): array
    {
        $rawDesc = trim((string) ($activity->description ?? ''));
        $eventRaw = strtolower((string) ($activity->event ?? ''));

        $props = [];
        if (isset($activity->properties)) {
            $props = is_array($activity->properties)
                ? $activity->properties
                : (method_exists($activity->properties, 'toArray') ? $activity->properties->toArray() : []);
        }

        $attrs = is_array($props['attributes'] ?? null) ? $props['attributes'] : [];
        $old = is_array($props['old'] ?? null) ? $props['old'] : [];

        $table = '';
        $extractedTarget = null;

        // Parse legacy technical pattern: "Data dari tabel {table} (Nama/ID: {target}) telah {event}"
        if (preg_match('/^Data dari tabel\s+([a-zA-Z0-9_]+)\s+\(Nama\/ID:\s*(.*?)\)\s+telah\s+([a-zA-Z0-9_]+)$/iu', $rawDesc, $matches)) {
            $table = strtolower(trim($matches[1]));
            $extractedTarget = trim($matches[2]);
            if ($eventRaw === '') {
                $eventRaw = strtolower(trim($matches[3]));
            }
        } elseif (!empty($activity->subject_type) && class_exists($activity->subject_type)) {
            try {
                $subjectInstance = new $activity->subject_type;
                if (method_exists($subjectInstance, 'getTable')) {
                    $table = strtolower($subjectInstance->getTable());
                }
            } catch (\Throwable $e) {
                $table = '';
            }
        }

        // Detect Backoffice Self-Login event on admins table
        $isLoginEvent = false;
        if ($table === 'admins' && $eventRaw === 'updated') {
            $sameActorAndSubject = !empty($activity->causer_id)
                && !empty($activity->subject_id)
                && (string) $activity->causer_id === (string) $activity->subject_id;

            $loginTimestampMatched = false;
            if (!empty($attrs['last_login_at']) && !empty($activity->created_at)) {
                try {
                    $loginTime = Carbon::parse($attrs['last_login_at']);
                    $actTime = Carbon::parse($activity->created_at);
                    $loginTimestampMatched = abs($loginTime->diffInSeconds($actTime)) <= 30;
                } catch (\Throwable $e) {
                    $loginTimestampMatched = false;
                }
            }

            if ($sameActorAndSubject || $loginTimestampMatched) {
                $isLoginEvent = true;
            }
        }

        if ($isLoginEvent || str_contains(strtolower($rawDesc), 'masuk (login) ke sistem')) {
            return [
                'description' => 'Masuk (login) ke sistem melalui fitur Autentikasi Backoffice',
                'badge_label' => 'LOGIN SISTEM',
                'badge_class' => 'tint-emerald',
                'module' => 'Autentikasi Backoffice',
            ];
        }

        // If description is already humanized (does not start with "Data dari tabel "), keep it and attach badge metadata
        if ($table === '' && !str_starts_with(strtolower($rawDesc), 'data dari tabel ')) {
            [$badgeLabel, $badgeClass] = self::resolveBadgeForEvent($eventRaw, $rawDesc);
            return [
                'description' => $rawDesc !== '' ? $rawDesc : 'Melakukan aktivitas operasional pada Sistem Backoffice',
                'badge_label' => $badgeLabel,
                'badge_class' => $badgeClass,
                'module' => 'Sistem Backoffice',
            ];
        }

        [, $moduleName] = self::getModuleMetadata($table);
        $narrative = self::composeHumanNarrative($table, $eventRaw, $extractedTarget, $attrs, $old, true);
        [$badgeLabel, $badgeClass] = self::resolveBadgeForEvent($eventRaw, $narrative, $table, $attrs);

        return [
            'description' => $narrative,
            'badge_label' => $badgeLabel,
            'badge_class' => $badgeClass,
            'module' => trim($moduleName),
        ];
    }

    /**
     * Compose a natural Indonesian sentence explaining WHAT action was taken and in WHICH feature/module.
     */
    protected static function composeHumanNarrative(
        string $table,
        string $eventRaw,
        ?string $rawTarget,
        array $attrs = [],
        array $secondaryAttrs = [],
        bool $isHistorical = true
    ): string {
        $table = strtolower($table);
        $event = strtolower($eventRaw);
        [$objectLabel, $moduleName] = self::getModuleMetadata($table);
        $moduleName = trim($moduleName);

        // Resolve clean human-readable target name (never show raw UUID)
        $cleanTarget = null;
        if (!self::isTechnicalIdentifier($rawTarget)) {
            $cleanTarget = trim((string) $rawTarget);
        } else {
            $candidates = [
                $attrs['name'] ?? null,
                $attrs['title'] ?? null,
                $attrs['payment_code'] ?? null,
                $attrs['code'] ?? null,
                $attrs['nis'] ?? null,
                $secondaryAttrs['name'] ?? null,
                $secondaryAttrs['title'] ?? null,
                $secondaryAttrs['payment_code'] ?? null,
            ];
            foreach ($candidates as $candidate) {
                if (is_scalar($candidate) && !self::isTechnicalIdentifier((string) $candidate)) {
                    $cleanTarget = trim((string) $candidate);
                    break;
                }
            }
        }

        // Special handling for Transactions (rich financial context without UUID)
        if ($table === 'transactions') {
            $txType = strtoupper((string) ($attrs['type'] ?? $secondaryAttrs['type'] ?? ''));
            $txStatus = strtoupper((string) ($attrs['status'] ?? ''));
            $txCode = $attrs['payment_code'] ?? $secondaryAttrs['payment_code'] ?? ($cleanTarget ?: null);
            $rawAmount = $attrs['pay_amount'] ?? $attrs['amount'] ?? $secondaryAttrs['pay_amount'] ?? null;
            $formattedAmount = (is_numeric($rawAmount) && (int) $rawAmount > 0)
                ? 'Rp ' . number_format((int) $rawAmount, 0, ',', '.')
                : null;

            $txObjectMap = [
                'BILL' => ['pembayaran tagihan santri', 'Transaksi & Tagihan'],
                'SALDO' => ['top-up saldo dompet santri', 'Dompet Digital Santri'],
                'SAVING' => ['setoran tabungan santri', 'Tabungan Santri'],
                'PPDB' => ['pembayaran pendaftaran PSB', 'Penerimaan Santri Baru (PSB)'],
            ];
            [$txLabel, $txModule] = $txObjectMap[$txType] ?? ['transaksi pembayaran', 'Transaksi & Keuangan'];

            $details = array_values(array_filter([$txCode, $formattedAmount]));
            $detailSuffix = !empty($details) ? ' (' . implode(' • ', $details) . ')' : '';

            if ($event === 'created') {
                return "Membuat {$txLabel}{$detailSuffix} pada fitur {$txModule}";
            }
            if ($event === 'deleted') {
                return "Menghapus data {$txLabel}{$detailSuffix} pada fitur {$txModule}";
            }
            if (!empty($attrs['is_deleted_from_archive'])) {
                return "Mengarsipkan data {$txLabel}{$detailSuffix} pada fitur Audit & Transaksi Keuangan";
            }
            if ($txStatus === 'PAID') {
                return "Memverifikasi lunas {$txLabel}{$detailSuffix} pada fitur {$txModule}";
            }
            if (in_array($txStatus, ['CANCELLED', 'REJECTED'], true)) {
                return "Membatalkan pengajuan {$txLabel}{$detailSuffix} pada fitur {$txModule}";
            }

            return "Memperbarui data {$txLabel}{$detailSuffix} pada fitur {$txModule}";
        }

        // Special handling for Saldo & Saving Histories
        if (in_array($table, ['saldo_histories', 'saving_histories'], true)) {
            $rawAmount = $attrs['amount'] ?? $secondaryAttrs['amount'] ?? null;
            $formattedAmount = (is_numeric($rawAmount) && (int) $rawAmount > 0)
                ? ' sebesar Rp ' . number_format((int) $rawAmount, 0, ',', '.')
                : '';
            $status = strtoupper((string) ($attrs['status'] ?? ''));

            if ($event === 'created') {
                return "Mencatat {$objectLabel}{$formattedAmount} pada fitur {$moduleName}";
            }
            if ($event === 'updated' && $status === 'SUCCESS') {
                return "Menyetujui {$objectLabel}{$formattedAmount} pada fitur {$moduleName}";
            }
            if ($event === 'deleted') {
                return "Menghapus {$objectLabel}{$formattedAmount} pada fitur {$moduleName}";
            }
            return "Memperbarui {$objectLabel}{$formattedAmount} pada fitur {$moduleName}";
        }

        $targetPhrase = $cleanTarget !== null ? " ({$cleanTarget})" : '';

        if ($event === 'created') {
            return "Menambahkan {$objectLabel} baru{$targetPhrase} pada fitur {$moduleName}";
        }
        if ($event === 'deleted') {
            return "Menghapus {$objectLabel}{$targetPhrase} pada fitur {$moduleName}";
        }

        return "Memperbarui {$objectLabel}{$targetPhrase} pada fitur {$moduleName}";
    }

    /**
     * Resolve human-friendly Indonesian badge label & semantic CSS class.
     *
     * @return array{0: string, 1: string}
     */
    protected static function resolveBadgeForEvent(
        string $eventRaw,
        string $narrative = '',
        string $table = '',
        array $attrs = []
    ): array {
        $event = strtolower($eventRaw);
        $narrativeLower = strtolower($narrative);

        if (str_contains($narrativeLower, 'masuk (login)')) {
            return ['LOGIN SISTEM', 'tint-emerald'];
        }
        if (str_contains($narrativeLower, 'memverifikasi lunas') || str_contains($narrativeLower, 'menyetujui')) {
            return ['VERIFIKASI', 'tint-emerald'];
        }
        if (str_contains($narrativeLower, 'membatalkan')) {
            return ['PEMBATALAN', 'tint-red'];
        }
        if (str_contains($narrativeLower, 'mengarsipkan')) {
            return ['ARSIP DATA', 'tint-slate'];
        }

        if ($event === 'created') {
            return ['TAMBAH DATA', 'tint-emerald'];
        }
        if ($event === 'deleted') {
            return ['HAPUS DATA', 'tint-red'];
        }
        if ($event === 'updated') {
            return ['PERUBAHAN', 'tint-primary'];
        }

        return ['AKTIVITAS', 'tint-slate'];
    }

    protected function parseDevice(string $userAgent): string
    {
        if (app()->runningInConsole()) {
            return 'System Console';
        }

        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $userAgent)) {
            return 'Tablet';
        }

        if (preg_match('/(up\.browser|up\.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile|iphone|ipad|ipod)/i', $userAgent)) {
            if (stripos($userAgent, 'iphone') !== false) {
                return 'iPhone';
            }
            if (stripos($userAgent, 'ipad') !== false) {
                return 'iPad';
            }
            if (stripos($userAgent, 'android') !== false) {
                return 'Android Mobile';
            }
            return 'Mobile';
        }

        if (stripos($userAgent, 'windows') !== false) {
            return 'Windows Desktop';
        }
        if (stripos($userAgent, 'macintosh') !== false || stripos($userAgent, 'mac os x') !== false) {
            return 'Mac Desktop';
        }
        if (stripos($userAgent, 'linux') !== false) {
            return 'Linux Desktop';
        }

        return 'Desktop';
    }
}

