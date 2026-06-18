<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\BiometricMapping;
use App\Models\Attendance;
use App\Models\PrayerTime;
use App\Models\EmployeeMonthlyShift;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BiometricAttendanceController extends Controller
{
    /**
     * 1. API Webhook untuk Mesin Fingerprint / Face Recognition Fisik
     */
    public function logWebhook(Request $request)
    {
        $token = $request->header('Authorization');
        $device = BiometricDevice::where('auth_token', $token)->first();

        if (!$device || !$device->is_active) {
            return response()->json(['message' => 'Alat biometrik tidak terdaftar atau tidak aktif.'], 401);
        }

        $request->validate([
            'logs' => 'required|array',
            'logs.*.pin' => 'required|string',
            'logs.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
            'logs.*.type' => 'required|in:in,out,auto',
        ]);

        $processed = 0;
        foreach ($request->logs as $log) {
            // Cari mapping PIN ke pengguna
            $mapping = BiometricMapping::where('device_pin', $log['pin'])->first();
            if (!$mapping) {
                continue;
            }

            $user = $mapping->presensiable;
            $timestamp = Carbon::parse($log['timestamp']);
            
            $this->processAttendance(
                $user, 
                $timestamp, 
                $log['type'], 
                $mapping->biometric_type, 
                $device->id
            );
            $processed++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil memproses $processed log absensi."
        ]);
    }

    /**
     * 2. API Mobile Check-in (PWA/Smartphone dengan GPS + Foto Face Recognition)
     */
    public function mobileCheckin(Request $request)
    {
        $request->validate([
            'activity_type' => 'required|in:school,prayer,kajian,work',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // Base64 image
            'activity_name' => 'nullable|string', // e.g. "Maghrib", "Shift Pagi"
            'schedule_id' => 'nullable|uuid', // jika kajian
        ]);

        $user = auth()->user(); // Bisa Admin atau User
        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak terautentikasi.'], 401);
        }

        // Simpan foto
        $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));
        $fileName = 'attendances/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($fileName, $photoData);
        $photoPath = Storage::url($fileName);

        $timestamp = Carbon::now();
        
        $attendance = $this->processAttendance(
            $user,
            $timestamp,
            'in',
            'face_recognition',
            null,
            $request->latitude,
            $request->longitude,
            $photoPath,
            $request->activity_type,
            $request->activity_name,
            $request->schedule_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Presensi berhasil dicatat via mobile.',
            'data' => $attendance
        ]);
    }

    /**
     * Core Engine untuk Memproses dan Mencocokkan Log Presensi
     */
    public function processAttendance(
        $user, 
        Carbon $timestamp, 
        $logType, 
        $method, 
        $deviceId = null, 
        $lat = null, 
        $lng = null, 
        $photoPath = null,
        $activityTypeOverride = null,
        $activityNameOverride = null,
        $scheduleId = null
    ) {
        $dateStr = $timestamp->toDateString();
        $timeStr = $timestamp->toTimeString();

        // 1. JIKA USER ADALAH SISWA / SANTRI
        if ($user instanceof \App\Models\Student) {
            // Default adalah kegiatan sekolah harian
            $activityType = $activityTypeOverride ?: 'school';
            $activityName = $activityNameOverride ?: 'Presensi Masuk Harian';
            $status = 'present';
            $lateMinutes = 0;

            // Jika override sholat atau dicocokkan otomatis berdasarkan jam
            if ($activityType === 'prayer' || !$activityTypeOverride) {
                // Cari sholat yang cocok dengan rentang waktu
                $prayer = $this->matchPrayerTime($timeStr);
                if ($prayer) {
                    $activityType = 'prayer';
                    $activityName = $prayer->prayer_name;

                    // Bandingkan dengan waktu iqomah masjid
                    $iqomahTime = Carbon::createFromTimeString($prayer->iqomah_time);
                    $checkInTime = Carbon::createFromTimeString($timeStr);

                    if ($checkInTime->gt($iqomahTime->copy()->addMinutes($prayer->grace_period))) {
                        $status = 'late';
                        $lateMinutes = $checkInTime->diffInMinutes($iqomahTime);
                    }
                }
            }

            // Jika kegiatan sekolah harian
            if ($activityType === 'school' && !$activityTypeOverride) {
                // Asumsi batas jam masuk sekolah adalah 07:00 dengan grace period 15 menit
                $schoolStartTime = Carbon::createFromTimeString('07:00:00');
                $checkInTime = Carbon::createFromTimeString($timeStr);
                
                if ($checkInTime->gt($schoolStartTime->copy()->addMinutes(15))) {
                    $status = 'late';
                    $lateMinutes = $checkInTime->diffInMinutes($schoolStartTime);
                }
            }

            // Simpan atau update kehadiran santri
            return Attendance::updateOrCreate(
                [
                    'presensiable_type' => get_class($user),
                    'presensiable_id' => $user->id,
                    'activity_type' => $activityType,
                    'activity_name' => $activityName,
                    'check_in' => $timestamp->copy()->startOfDay() // Simpan record per hari
                ],
                [
                    'check_in' => $timestamp,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'method' => $method,
                    'device_id' => $deviceId,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'photo_path' => $photoPath,
                    'schedule_id' => $scheduleId,
                ]
            );
        } 

        // 2. JIKA USER ADALAH KARYAWAN (ADMIN ATAU USER)
        else {
            $activityType = 'work';
            $activityName = $activityNameOverride ?: 'Jam Kerja';
            
            // Cari shift kerja terjadwal karyawan hari ini (dari scheduler bulanan)
            $monthlyShift = EmployeeMonthlyShift::with('workingShift')
                ->where('presensiable_type', get_class($user))
                ->where('presensiable_id', $user->id)
                ->where('date', $dateStr)
                ->first();

            if (!$monthlyShift || $monthlyShift->is_holiday || !$monthlyShift->workingShift) {
                // Pengguna tidak dijadwalkan masuk atau hari libur
                return null;
            }

            $shift = $monthlyShift->workingShift;
            $status = 'present';
            $lateMinutes = 0;

            // Jika check-in (atau logType adalah 'in' / 'auto' dan belum ada log checkin hari ini)
            $existingAttendance = Attendance::where('presensiable_type', get_class($user))
                ->where('presensiable_id', $user->id)
                ->where('activity_type', 'work')
                ->whereDate('check_in', $dateStr)
                ->first();

            if (!$existingAttendance && ($logType === 'in' || $logType === 'auto')) {
                // Hitung keterlambatan
                $shiftStartTime = Carbon::createFromTimeString($shift->start_time);
                $checkInTime = Carbon::createFromTimeString($timeStr);

                if ($checkInTime->gt($shiftStartTime->addMinutes($shift->grace_period))) {
                    $status = 'late';
                    $lateMinutes = $checkInTime->diffInMinutes($shiftStartTime);
                }

                return Attendance::create([
                    'presensiable_type' => get_class($user),
                    'presensiable_id' => $user->id,
                    'activity_type' => 'work',
                    'activity_name' => $shift->name,
                    'check_in' => $timestamp,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'method' => $method,
                    'device_id' => $deviceId,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'photo_path' => $photoPath,
                ]);
            } 
            
            // Jika check-out (atau logType adalah 'out' / 'auto' dan sudah ada check-in)
            elseif ($existingAttendance && ($logType === 'out' || $logType === 'auto')) {
                $existingAttendance->update([
                    'check_out' => $timestamp,
                ]);
                return $existingAttendance;
            }

            return null;
        }
    }

    /**
     * Mencocokkan waktu tap ke sholat berjamaah terdekat
     */
    private function matchPrayerTime($timeStr)
    {
        $time = Carbon::createFromTimeString($timeStr);

        // Cari prayer times dari database
        $prayers = PrayerTime::all();

        foreach ($prayers as $prayer) {
            $adzan = Carbon::createFromTimeString($prayer->adzan_time);
            
            // Rentang waktu pencocokan sholat (misal 30 menit sebelum adzan s/d 1 jam setelah iqomah)
            $startWindow = $adzan->copy()->subMinutes(30);
            $endWindow = Carbon::createFromTimeString($prayer->iqomah_time)->addHour();

            if ($time->between($startWindow, $endWindow)) {
                return $prayer;
            }
        }

        return null;
    }
}
