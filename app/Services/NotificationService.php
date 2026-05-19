<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kutia\Larafirebase\Facades\Larafirebase;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

/**
 * Class NotificationService.
 */
class NotificationService
{

    public static function sendToTopic($title, $body, $topic, $payload = null, $image = null)
    {
        $payload ? $type = get_class($payload) : $type = null;
        $payload['notification_type'] =  str_replace('App\\Models\\', '', $type);
        $messaging = app('firebase.messaging');
        $user = User::whereNotNull('fcm_token')->get()->pluck('id');
        $data = [];
        foreach ($user as $value) {
            $data[] = [
                'id'        => (string) Str::uuid(),
                'title'     => $title,
                'body'      => $body,
                'user_id'   => $value,
                'payload'   => $payload,
                'type'      => str_replace('App\\Models\\', '', $type),
                'reference_id' => $payload->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        $chunks = array_chunk($data, 1000);
        foreach ($chunks as $value) {
            Notification::insert($value);
        }
        $notification = FirebaseNotification::create($title, $body);
        if (!empty($image)) {
            $notification = $notification->withImageUrl($image);
        }
        $message = CloudMessage::fromArray([
            'topic' => $topic,
            'notification' => $notification,
            'data' => ['type' => $topic . '_' . env('APP_BROADCAST_TYPE')],
            'android' => [
                'notification' => [
                    'sound' => 'default',
                    'channel_id' => 'id.cahayatasbih.app',
                ]
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                    ]
                ]
            ]
        ]);
        $messaging->send($message);
        return $message;
    }

    public static function sendTo($title, $body, $user, $payload = null, $image = null)
    {
        try {
            $messaging = app('firebase.messaging');

            // Convert $payload to a string representation if it's an array
            $payloadString = is_array($payload) ? json_encode($payload) : $payload;

            $type = $payload ? class_basename($payload) : null;

            $data = [
                'id' => (string) Str::uuid(),
                'title'      => $title,
                'body'       => $body,
                'payload'    => $payloadString,
                'type'       => $type ? str_replace('App\\Models\\', '', $type) : null,
                'reference_id'  => $payload ? $payload->id : null,
                'channel_id' => 'id.cahayatasbih.app',
                'user_id'    => $user->id,
            ];

            if ($user->fcm_token) {
                // Ensure all values in data payload are strings
                $fcmData = [];
                foreach ($data as $key => $val) {
                    if (is_array($val) || is_object($val)) {
                        $fcmData[$key] = json_encode($val);
                    } else {
                        $fcmData[$key] = (string) $val;
                    }
                }

                $message = CloudMessage::fromArray([
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $fcmData,
                    'android' => [
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'id.cahayatasbih.app',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ]
                        ]
                    ]
                ]);

                $messaging->send($message);
            }

            // Simpan riwayat notifikasi
            Notification::create($data);

            return true;
        } catch (\Throwable $th) {
            Log::error($th);
            return false;
        }
    }

    public static function sendWithHistory($title, $body, $user, $payload = null, $image)
    {
        try {
            $messaging = app('firebase.messaging');

            // Convert $payload to a string representation if it's an array
            $payloadString = is_array($payload) ? json_encode($payload) : $payload;

            $type = $payload ? get_class($payload) : null;

            $payloadId = null;
            if ($payload && property_exists($payload, 'id')) {
                $payloadId = $payload->id;
            }

            $payload['notification_type'] = str_replace('App\\Models\\', '', $type);
            $data = [
                'title'      => $title,
                'body'       => $body,
                'payload'    => $payloadString,
                'type'       => str_replace('App\\Models\\', '', $type),
                'reference_id'  => $payloadId,
                'channel_id' => 'id.cahayatasbih.app',
                'user_id'    => $user->id,
            ];
            Notification::create($data);

            if ($user->fcm_token) {
                // Ensure all values in data payload are strings
                $fcmData = [];
                foreach ($data as $key => $val) {
                    if (is_array($val) || is_object($val)) {
                        $fcmData[$key] = json_encode($val);
                    } else {
                        $fcmData[$key] = (string) $val;
                    }
                }

                $message = CloudMessage::fromArray([
                    'token' => $user->fcm_token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $fcmData,
                    'android' => [
                        'notification' => [
                            'sound' => 'default',
                            'channel_id' => 'id.cahayatasbih.app',
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ]
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ]
                        ]
                    ]
                ]);

                return $messaging->send($message);
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            Log::error($th);
            return $th;
        }
    }

    public static function sendSome($title, $body, $users, $payload = null, $image = null)
    {
        try {
            $messaging = app('firebase.messaging');
            $data = [];

            foreach ($users as $user) {
                $type = $payload ? class_basename($payload) : 'Notification';

                $item = [
                    'id' => (string) Str::uuid(),
                    'title' => $title,
                    'body' => $body,
                    'user_id' => $user->id,
                    'payload' => $payload,
                    'type' => $type,
                    'reference_id' => $payload ? $payload->id : null,
                    // 'channel_id' => 'id.cahayatasbih.app',
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString(),
                ];

                $data[] = $item;

                if ($user->fcm_token) {
                    // Ensure all values in data payload are strings
                    $fcmData = [];
                    foreach ($item as $key => $val) {
                        if (is_array($val) || is_object($val)) {
                            $fcmData[$key] = json_encode($val);
                        } else {
                            $fcmData[$key] = (string) $val;
                        }
                    }

                    $message = CloudMessage::fromArray([
                        'token' => $user->fcm_token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => $fcmData,
                        'android' => [
                            'notification' => [
                                'sound' => 'default',
                                'channel_id' => 'id.cahayatasbih.app',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ]
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'badge' => 1,
                                ]
                            ]
                        ]
                    ]);

                    $messaging->send($message);
                }
            }

            $chunks = array_chunk($data, 5000);

            foreach ($chunks as $chunk) {
                // Simpan riwayat notifikasi
                Notification::insert($chunk);
            }

            return $messaging;
        } catch (\Exception $e) {
            Log::error('Error in sendSome function: ' . $e->getMessage());
            return null;
        }
    }

    public static function sendToTrainer($title, $body, $trainer, $payload = null, $image = null)
    {
        try {
            if ($trainer->token) {
                return Larafirebase::fromArray(['title' => $title, 'body' => $body, 'data' => $payload])
                    ->sendMessage($trainer->token);
            } else {
                return false;
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }
}
