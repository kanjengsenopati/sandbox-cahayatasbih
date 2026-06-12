<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


trait LogActivityTrait
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('system')
            ->setDescriptionForEvent(function (string $eventName) {
                $name = $this->name ?? $this->title ?? $this->id ?? 'Data';
                return "Data dari tabel " . (new static)->getTable() . " (Nama/ID: {$name}) telah {$eventName}";
            })
            ->logFillable()
            ->tapActivity(function($activity, $eventName) {
                $userAgent = request()->userAgent() ?? 'Unknown';
                $device = $this->parseDevice($userAgent);

                $activity->properties = $activity->properties->merge([
                    'device' => $device,
                    'ip' => request()->ip(),
                ]);
            });
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
