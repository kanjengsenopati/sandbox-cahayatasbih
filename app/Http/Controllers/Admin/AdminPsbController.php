<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendToPushNotificationJob;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Contact;
use App\Models\PpdbRegistration;
use App\Models\PpdbTrack;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\SendNotifWaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminPsbController extends Controller
{
    /**
     * Display a listing of PSB registrations.
     * Features: Filtering, Searching, Statistics
     */
    public function index(Request $request)
    {
        // Build the base query with eager loading
        $query = PpdbRegistration::with(['user', 'track.school', 'track.ppdbWave.academicYear'])
            ->latest();

        // Filter by Academic Year (via ppdb_waves -> academic_year_id)
        if ($request->filled('academic_year')) {
            $query->whereHas('track.ppdbWave', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year);
            });
        }

        // Filter by School Level (SMP/MA)
        if ($request->filled('school_level')) {
            $query->whereHas('track.school', function ($q) use ($request) {
                $q->where('type', $request->school_level);
            });
        }

        // Filter by Track Type (UMUM/JAMAAH/ALUMNI)
        if ($request->filled('track_type')) {
            $query->whereHas('track', function ($q) use ($request) {
                $q->where('registration_type', $request->track_type);
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by Santri Name, Registration Code, or Parent Phone
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('registration_code', 'like', "%{$searchTerm}%")
                    ->orWhere('parent_phone', 'like', "%{$searchTerm}%");
            });
        }

        // Get paginated results
        $registrations = $query->paginate(15);

        // Prepare filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $schools = School::whereIn('type', ['SMP', 'MA'])->get();
        $trackTypes = [
            PpdbTrack::TYPE_UMUM => 'Umum (Non-Jamaah)',
            PpdbTrack::TYPE_JAMAAH => 'Jamaah MDTI',
            PpdbTrack::TYPE_ALUMNI => 'Alumni',
        ];
        $statuses = [
            'DRAFT' => 'Draft',
            'SUBMITTED' => 'Menunggu Verifikasi',
            'VERIFIED' => 'Terverifikasi',
            'ACCEPTED' => 'Diterima',
            'REJECTED' => 'Ditolak',
        ];

        // Statistics for top cards
        $stats = [
            'total' => PpdbRegistration::count(),
            'pending' => PpdbRegistration::whereIn('status', ['SUBMITTED', 'VERIFIED'])->count(),
            'accepted' => PpdbRegistration::where('status', 'ACCEPTED')->count(),
            'rejected' => PpdbRegistration::where('status', 'REJECTED')->count(),
        ];

        return view('admins.psb.index', compact(
            'registrations',
            'academicYears',
            'schools',
            'trackTypes',
            'statuses',
            'stats'
        ));
    }

    /**
     * Display the specified registration detail.
     * Includes KTA verification data for Jamaah track.
     */
    public function show($id)
    {
        $registration = PpdbRegistration::with([
            'user',
            'track.school',
            'track.ppdbWave.academicYear'
        ])->findOrFail($id);

        // Additional data for Jamaah track verification
        $ktaData = null;
        if ($registration->track && $registration->track->registration_type === PpdbTrack::TYPE_JAMAAH) {
            $user = $registration->user;
            if ($user) {
                $ktaData = [
                    'kta_image_path' => $user->kta ?? null,
                    'status' => $user->status ?? 'PENDING',
                    'member_branch' => $user->member_branch ?? null,
                    'member_group' => $user->member_group ?? null,
                ];
            }
        }

        // Check if this is an Alumni track (for displaying auto-verified badge)
        $isAlumniTrack = $registration->track &&
            $registration->track->registration_type === PpdbTrack::TYPE_ALUMNI;

        return view('admins.psb.show', compact('registration', 'ktaData', 'isAlumniTrack'));
    }

    /**
     * Update the registration status (Accept or Reject).
     * If accepted, create a Student record automatically.
     */
    // public function updateStatus(Request $request, $id)
    // {
    //     $request->validate([
    //         'action' => 'required|in:ACCEPTED,REJECTED',
    //         'admin_note' => 'required_if:action,REJECTED|nullable|string|max:500',
    //         'classroom_id' => 'required_if:action,ACCEPTED|nullable|exists:classrooms,id',
    //     ], [
    //         'admin_note.required_if' => 'Alasan penolakan wajib diisi.',
    //         'classroom_id.required_if' => 'Kelas wajib dipilih untuk santri yang diterima.',
    //     ]);

    //     $registration = PpdbRegistration::with(['track.school', 'track.ppdbWave.academicYear', 'user'])->findOrFail($id);

    //     DB::beginTransaction();
    //     try {
    //         if ($request->action === PpdbRegistration::STATUS_ACCEPTED) {
    //             DB::transaction(function () use ($registration, $request) {
    //                 $student = $this->acceptRegistration($registration, $request);
    //                 $this->sendAcceptedNotifications($registration, $student);
    //             });

    //             return redirect()
    //                 ->route('psb.show', $registration->id)
    //                 ->with('success', 'Santri berhasil diterima dan data telah dimigrasikan ke sistem.');
    //         } else {
    //             // REJECTED
    //             $registration->update([
    //                 'status' => 'REJECTED',
    //                 'admin_note' => $request->admin_note,
    //             ]);

    //             DB::commit();
    //             return redirect()
    //                 ->route('psb.show', $id)
    //                 ->with('success', 'Pendaftaran telah ditolak.');
    //         }
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return redirect()
    //             ->back()
    //             ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    //     }
    // }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:ACCEPTED,REJECTED',
            'admin_note' => 'required_if:action,REJECTED|nullable|string|max:500',
            'classroom_id' => 'required_if:action,ACCEPTED|nullable|exists:classrooms,id',
        ], [
            'admin_note.required_if' => 'Alasan penolakan wajib diisi.',
            'classroom_id.required_if' => 'Kelas wajib dipilih untuk santri yang diterima.',
        ]);

        $registration = PpdbRegistration::with(['track.school', 'track.ppdbWave.academicYear', 'user'])
            ->findOrFail($id);

        try {
            DB::transaction(function () use ($request, $registration) {
                if ($request->action === PpdbRegistration::STATUS_ACCEPTED) {
                    $student = $this->acceptRegistration($registration, $request);
                    $this->sendAcceptedNotifications($registration, $student);
                }

                if ($request->action === PpdbRegistration::STATUS_REJECTED) {
                    $this->rejectRegistration($registration, $request);
                }
            });

            return redirect()
                ->route('psb.show', $registration->id)
                ->with('success', 'Status pendaftaran berhasil diperbarui.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function rejectRegistration($registration, $request)
    {
        $registration->update([
            'status' => PpdbRegistration::STATUS_REJECTED,
            'admin_note' => $request->admin_note,
        ]);

        $this->sendRejectedNotifications($registration);
    }


    protected function acceptRegistration($registration, $request)
    {
        $registration->update([
            'status' => PpdbRegistration::STATUS_ACCEPTED,
            'admin_note' => $request->admin_note,
        ]);

        $student = $this->createStudentFromRegistration(
            $registration,
            $request->classroom_id
        );

        if ($registration->track?->installment_plan) {
            app(\App\Services\PsbBillingService::class)
                ->generateBillsForStudent($student, $registration->track);
        }

        return $student;
    }

    protected function sendAcceptedNotifications($registration, $student)
    {
        $messageWhatsapp = SendNotifWaService::sendMessageAcceptedPpdb($registration);

        dispatch(new SendToPushNotificationJob(
            'PPDB Berhasil – Lanjut Daftar Ulang',
            'Selamat! Pendaftaran PPDB kamu telah berhasil. Silakan lakukan daftar ulang sesuai jadwal.',
            $student->user,
            $registration
        ));

        dispatch(new SendToWhatsappNotificationJob(
            $student->user->phone,
            $messageWhatsapp
        ));

        Contact::whereIn('type', [
            Contact::TYPE_BENDAHARA,
            Contact::TYPE_SUPERADMIN,
        ])->each(function ($contact) use ($messageWhatsapp) {
            dispatch(new SendToWhatsappNotificationJob(
                $contact->phone,
                $messageWhatsapp
            ));
        });
    }

    protected function sendRejectedNotifications($registration)
    {
        $messageWhatsapp = SendNotifWaService::sendMessageRejectedPpdb($registration);

        dispatch(new SendToPushNotificationJob(
            'PPDB Ditolak',
            'Mohon maaf, pendaftaran PPDB belum dapat kami terima. Silakan lihat keterangan penolakan.',
            $registration->user,
            $registration
        ));

        dispatch(new SendToWhatsappNotificationJob(
            $registration->user->phone,
            $messageWhatsapp
        ));
    }


    /**
     * Verify KTA for Jamaah track registrations.
     */
    public function verifyKta(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'status' => 'VERIFIED',
        ]);

        return redirect()
            ->back()
            ->with('success', 'KTA Jamaah berhasil diverifikasi.');
    }

    /**
     * Get classrooms for a specific school (AJAX endpoint).
     */
    public function getClassrooms($schoolId)
    {
        $classrooms = Classroom::where('school_id', $schoolId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($classrooms);
    }

    /**
     * Create a Student record from the PpdbRegistration data.
     */
    private function createStudentFromRegistration(PpdbRegistration $registration, $classroomId): Student
    {
        // Get the school from the track
        $schoolId = $registration->track->school_id ?? null;

        // Generate NIS (format: YYMM + 4 random digits)
        $nis = date('ym') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // Build complete address
        $fullAddress = collect([
            $registration->address_street,
            'RT ' . $registration->rt . '/RW ' . $registration->rw,
            $registration->village,
            $registration->district,
            $registration->city,
            $registration->postal_code,
        ])->filter()->implode(', ');

        // Create the student
        return Student::create([
            'nisn' => $registration->nisn,
            'nis' => $nis,
            'user_id' => $registration->user_id,
            'school_id' => $schoolId,
            'classroom_id' => $classroomId,
            'name' => $registration->name,
            'born_place' => $registration->birth_place,
            'birth_date' => $registration->birth_date,
            'gender' => $registration->gender,
            'saldo' => 0,
            'avatar' => null,
            'is_blocked' => false,
            'daily_limit' => 50000, // Default daily limit
            'saving' => 0,
            'status' => Student::STATUS_ACTIVE,
            'address' => $fullAddress,
        ]);
    }
}
