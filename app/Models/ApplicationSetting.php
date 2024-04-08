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
        'payment_expire_time',
        'link_whatsapp',
        'number_whatsapp',
        'device_id',
        'bill_fee',
        'saldo_fee',
        'target_month',
        'target_year',
    ];

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
