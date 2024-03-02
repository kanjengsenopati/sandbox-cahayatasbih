<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\ApplicationSetting;


class SendNotifWaService
{
    public static function sendMessage($number, $message)
    {
        $app_setting = ApplicationSetting::first();
        $url = $app_setting->link_whatsapp;
        $deviceId = $app_setting->device_id;

        $client = new Client();

        try {
            $response = $client->get($url, [
                'query' => [
                    'device_id' => $deviceId,
                    'number' => $number,
                    'message' => $message,
                ],
            ]);

            $result = $response->getBody()->getContents();

            return "<pre>" . print_r($result, true);
        } catch (\Exception $e) {
            // Handle exception
            return $e->getMessage();
        }
    }
}
