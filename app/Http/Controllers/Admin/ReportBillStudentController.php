<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Bill;
use App\Models\School;
use App\Models\Student;
use App\Models\BillType;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\SendNotifWaService;
use Illuminate\Support\Facades\Auth;
use App\Models\StudentBillNotification;
use App\Jobs\SendToWhatsappNotificationJob;
use App\Jobs\SendBillWhatsappNotificationJob;

class ReportBillStudentController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Laporan Tagihan')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = Bill::when(request()->filled('start_date'), function ($query) {
                // Get the month and year from the start_date
                $startDate = Carbon::parse(request()->start_date);

                // Query for the start date year and month
                $query->where(function ($subQuery) use ($startDate) {
                    $subQuery->where('year', '>', $startDate->year)
                        ->orWhere(function ($subSubQuery) use ($startDate) {
                            $subSubQuery->where('year', '=', $startDate->year)
                                ->where('month', '>=', $startDate->month);
                        });
                });
            })
                ->when(request()->filled('end_date'), function ($query) {
                    // Get the month and year from the end_date
                    $endDate = Carbon::parse(request()->end_date);

                    // Query for the end date year and month
                    $query->where(function ($subQuery) use ($endDate) {
                        $subQuery->where('year', '<', $endDate->year)
                            ->orWhere(function ($subSubQuery) use ($endDate) {
                                $subSubQuery->where('year', '=', $endDate->year)
                                    ->where('month', '<=', $endDate->month);
                            });
                    });
                })
                ->schoolFilter('school_id', request()->school_id)
                ->classroomFilter('classroom_id', request()->classroom_id)
                ->when(request()->filled('bill_type_id'), function ($query) {
                    $query->whereIn('bill_type_id', request()->bill_type_id); // Menggunakan whereIn untuk array
                })
                // Apply the status filter only if it's filled
                ->when(request()->filled('status'), function ($query) {
                    $query->where('status', request()->status);
                })
                // Apply search on student name if provided
                ->when(request()->has('search') && is_array(request()->search) && isset(request()->search['value']), function ($query) {
                    $searchTerm = request()->search['value'];
                    $query->whereHas('student', function ($subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', '%' . $searchTerm . '%');
                    });
                })
                ->latest();

            if (request()->data == 'total') {
                // Fetch all types of transactions and sum them separately
                $totals = $data->selectRaw("
                SUM(CASE WHEN status = '" . Bill::STATUS_PAID . "' THEN amount ELSE 0 END) as total_paid,
                SUM(CASE WHEN status = '" . Bill::STATUS_UNPAID . "' THEN amount ELSE 0 END) as total_unpaid,
                SUM(amount) as total
            ")->first();

                // Format the totals using number_format
                $formattedTotalPaid = number_format($totals->total_paid, 0, ',', '.');
                $formattedTotalUnpaid = number_format($totals->total_unpaid, 0, ',', '.');
                $formattedTotal = number_format($totals->total, 0, ',', '.');

                return response()->json([
                    'total_paid' => $formattedTotalPaid,
                    'total_unpaid' => $formattedTotalUnpaid,
                    'target_revenue' => $formattedTotal
                ]);
            } elseif (request()->data == 'table') {
                return DataTables::of($data)
                    ->addColumn('amount', function ($data) {
                        return 'Rp' . number_format($data->amount, 0, ',', '.');
                    })
                    ->addColumn('bill_type', function ($data) {
                        return $data->billType?->name ?? '-';
                    })
                    ->addColumn('status', function ($data) {
                        return $data->status == 'UNPAID' ? '<span class="badge badge-danger">Belum Lunas</span>' : '<span class="badge badge-success">Lunas</span>';
                    })
                    ->addColumn('student', function ($data) {
                        $studentName = $data->student?->name ? $data->student->name : '-';
                        $className = $data->classroom?->name ? $data->classroom->name : '-';

                        // Check if avatar exists, if not, use default avatar
                        $avatarUrl = $data->student?->avatar ? $data->student->avatar : asset('assets/media/avatars/default.png');

                        // Return HTML structure for the card with avatar, name, and class
                        return '<div class="student-card" style="display: flex; align-items: center; gap: 10px;">
                        <img src="' . $avatarUrl . '" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <div><strong>' . $studentName . '</strong></div>
                            <div>' . $className . '</div>
                        </div>
                    </div>';
                    })
                    ->addColumn('period', function ($data) {
                        // Array of month names in Indonesian
                        $months = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember'
                        ];

                        // Add the translated month and year
                        return $months[$data->month] . ' ' . $data->year;
                    })
                    ->addColumn('notification', function ($data) {
                        // Retrieve the notification status for the student
                        $notification = $data?->student?->studentBillNotifications?->first();

                        // Determine the badge content based on the notification status
                        $badge = $notification
                            ? '<span class="badge badge-success">Dikirim ' . Carbon::parse($notification->sent_at)->diffForHumans() . '</span>'
                            : '<span class="badge badge-danger">Belum Dikirim</span>';

                        return $badge;
                    })
                    ->addColumn('action', function ($data) {
                        $actionDelete = route('report-transaction.destroy', $data->id);
                        return "<div class='d-flex gap-2 flex-nowrap justify-content-center'>" .
                            // add icon print invoice
                            view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Laporan Transaksi']) .
                            "</div>";
                        // add delete action
                    })
                    ->rawColumns(['date', 'amount', 'action', 'student', 'status', 'notification'])
                    ->make(true);
            }
        }

        $schools = School::orderBy('name')->get();
        $billTypes = BillType::select('id', 'name')->whereNotIn('id', [
            '02dae620-fc2c-4bf2-9e13-c5c1950e4d48',
            '615a34af-be2d-45f2-9830-720fea341a0c',
            'f3a25c77-f8c0-4882-8286-571bc57bf87c',
            'ce389861-40ab-4523-9364-3458e9dfda1d'
        ])->get();
        return view('admins.report-bill-student.index', compact('schools', 'billTypes'));
    }

    public function sendBillWhatsappNotification()
    {
        try {
            // Ambil data siswa yang memiliki tagihan belum dibayar
            $students = Student::whereHas('bills', function ($query) {
                $query->where('status', Bill::STATUS_UNPAID);
            })->latest()->get();

            // Iterasi setiap siswa
            foreach ($students as $index => $student) {
                // Panggil fungsi untuk mendapatkan pesan
                $message = SendNotifWaService::sendAllBillInvoice($student);

                // Periksa apakah $message tidak null dan nomor telepon tersedia
                if ($message !== null && $student->user?->phone) {
                    // Kirim notifikasi dengan delay 1 detik per siswa
                    dispatch(new SendToWhatsappNotificationJob($student->user->phone, $message))
                        ->delay(now()->addSeconds($index)); // Delay bertambah sesuai urutan siswa

                    // Simpan atau perbarui log notifikasi ke tabel student_bill_notifications
                    StudentBillNotification::updateOrCreate(
                        [
                            'student_id' => $student->id, // Kunci unik untuk mencari entri
                        ],
                        [
                            'message' => $message, // Data yang akan diperbarui atau dibuat
                            'status' => StudentBillNotification::STATUS_SUCCESS,
                            'sent_at' => now(),
                        ]
                    );
                }
            }

            // Kembalikan respons sukses
            return response()->json(['success' => true, 'message' => 'WA Blast berhasil dikirim.']);
        } catch (Exception $e) {
            // Tangani kesalahan dan kembalikan respons error
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim WA Blast: ' . $e->getMessage(),
            ], 500); // HTTP status code 500 untuk server error
        }
    }
}
