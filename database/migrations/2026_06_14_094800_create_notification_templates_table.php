<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title_template');
            $table->text('body_template');
            $table->timestamps();
        });

        // Seed default templates
        DB::table('notification_templates')->insert([
            [
                'key' => 'payment_success',
                'title_template' => 'Yeay!, Pembayaran Berhasil',
                'body_template' => 'Pembayaran di Pondok Pesantren Cahaya Tasbih berhasil! Terima kasih telah membayar tagihan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'payment_rejected',
                'title_template' => 'Bukti Pembayaran Ditolak',
                'body_template' => 'Maaf, Bukti pembayaran anda ditolak. Silahkan upload ulang bukti pembayaran',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ppdb_success',
                'title_template' => 'PPDB Berhasil – Lanjut Daftar Ulang',
                'body_template' => 'Selamat! Pendaftaran PPDB kamu telah berhasil. Silakan lakukan daftar ulang sesuai jadwal.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'ppdb_rejected',
                'title_template' => 'PPDB Ditolak',
                'body_template' => 'Mohon maaf, pendaftaran PPDB belum dapat kami terima. Silakan lihat keterangan penolakan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'permit_new',
                'title_template' => 'Pengajuan Perizinan Baru',
                'body_template' => 'Wali dari {student_name} mengajukan izin {permit_type}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'permit_return_report',
                'title_template' => 'Laporan Kepulangan Santri',
                'body_template' => 'Wali dari {student_name} melaporkan bahwa santri telah kembali. Butuh konfirmasi Anda.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'permit_approved',
                'title_template' => 'Status Perizinan Disetujui',
                'body_template' => 'Pengajuan izin {student_name} telah disetujui oleh Ustadz.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'permit_rejected',
                'title_template' => 'Status Perizinan Ditolak',
                'body_template' => 'Pengajuan izin {student_name} ditolak. Alasan: {rejection_reason}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'permit_return_approved',
                'title_template' => 'Konfirmasi Kepulangan Disetujui',
                'body_template' => 'Laporan kepulangan santri {student_name} telah disetujui & dikonfirmasi oleh Ustadz.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'permit_return_rejected',
                'title_template' => 'Konfirmasi Kepulangan Ditolak',
                'body_template' => 'Laporan kepulangan santri {student_name} ditolak. Alasan: {rejection_reason}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'balance_update',
                'title_template' => 'Pemberitahuan Saldo',
                'body_template' => 'Santri {student_name} telah melakukan {activity} sebesar Rp. {amount}, Saldo Terkini Rp. {balance}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'saving_update',
                'title_template' => 'Pemberitahuan Tabungan',
                'body_template' => 'Santri {student_name} {description} menjadi Rp. {saving}',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
