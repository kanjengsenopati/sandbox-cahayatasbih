<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCardReport extends Model
{
    use HasFactory;

    protected $table = 'student_card_reports';

    protected $fillable = [
        'student_id',
        'reported_by',
        'issue_type',
        'notes',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public const ISSUE_RUSAK = 'rusak';
    public const ISSUE_TIDAK_BISA_TRANSAKSI = 'tidak_bisa_transaksi';
    public const ISSUE_HILANG = 'hilang';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(Admin::class, 'reported_by');
    }

    public function processedBy()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function getIssueLabelAttribute(): string
    {
        return match($this->issue_type) {
            self::ISSUE_RUSAK => 'Kartu Rusak',
            self::ISSUE_TIDAK_BISA_TRANSAKSI => 'Tidak Bisa Transaksi',
            self::ISSUE_HILANG => 'Kartu Hilang',
            default => ucfirst(str_replace('_', ' ', $this->issue_type ?? '-')),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu Pembuatan Ulang',
            self::STATUS_COMPLETED => 'Selesai Dicetak Ulang',
            self::STATUS_REJECTED => 'Ditolak',
            default => ucfirst($this->status ?? '-'),
        };
    }
}
