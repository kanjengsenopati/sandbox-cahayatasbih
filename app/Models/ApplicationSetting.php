<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicationSetting extends Model
{
    use HasFactory, UuidTrait, SoftDeletes;

    protected $fillable = [
        'payment_fee',
        'student_card_image',
        'student_card_layout',
        'payment_expire_time',
        'link_whatsapp',
        'number_whatsapp',
        'device_id',
        'bill_fee',
        'saldo_fee',
        'target_month',
        'target_year',
        'payment_auto_check',
    ];

    protected $casts = [
        'student_card_layout' => 'array',
        'payment_auto_check' => 'boolean',
    ];

    public static function getDefaultStudentCardLayout(): array
    {
        return [
            'logo' => [
                'show' => true,
                'top' => 5,
                'left' => 5,
                'width' => 25,
                'height' => 8,
            ],
            'title' => [
                'show' => true,
                'text' => 'Kartu Santri',
                'color' => '#FFFF00',
                'font_size' => 12,
                'top' => 5,
                'left' => 45,
                'text_align' => 'right',
                'font_weight' => 'bold',
            ],
            'subtitle' => [
                'show' => true,
                'text' => 'PPTQ Cahaya Tasbih',
                'color' => '#FFFFFF',
                'font_size' => 10,
                'top' => 10,
                'left' => 45,
                'text_align' => 'right',
                'font_weight' => 'bold',
            ],
            'photo' => [
                'show' => false,
                'top' => 18,
                'left' => 5,
                'width' => 18,
                'height' => 24,
                'border_radius' => 2,
            ],
            'name' => [
                'show' => true,
                'color' => '#FFFFFF',
                'font_size' => 12,
                'top' => 20,
                'left' => 25,
                'font_weight' => 'bold',
            ],
            'nis' => [
                'show' => true,
                'color' => '#FFFFFF',
                'font_size' => 14,
                'top' => 27,
                'left' => 25,
                'font_weight' => 'bold',
                'font_family' => 'Kredit',
            ],
            'classroom' => [
                'show' => true,
                'color' => '#FFFFFF',
                'font_size' => 9,
                'top' => 35,
                'left' => 25,
                'font_weight' => 'bold',
            ],
            'school' => [
                'show' => true,
                'color' => '#FFFFFF',
                'font_size' => 9,
                'top' => 40,
                'left' => 25,
                'font_weight' => 'bold',
            ],
            'code' => [
                'show' => true,
                'type' => 'barcode',
                'top' => 42,
                'left' => 55,
                'width' => 26,
                'height' => 8,
            ],
        ];
    }

    protected $appends = [
        'whatsapp_status'
    ];

    public function getWhatsappStatusAttribute()
    {
        $device_id = $this->device_id;
        $link = $this->link_whatsapp;
        if ($device_id) {
            $url = $link . 'statusDevice?device_id=' . $device_id;
            // add header to get data from api
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get($url);
            return $response->json()['status'];
        }
        return null;
    }

    public function getPaymentExpireTimeInMinutesAttribute(): int
    {
        // Explode the time string to get hours and minutes
        [$hours, $minutes] = explode(':', $this->attributes['payment_expire_time']);

        // Konversi jam ke menit dan tambahkan dengan menit, lalu kembalikan jumlah total menit
        return ($hours * 60) + $minutes;
    }
}
