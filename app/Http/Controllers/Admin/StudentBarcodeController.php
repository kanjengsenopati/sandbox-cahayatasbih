<?php

namespace App\Http\Controllers\Admin;

use ZipArchive;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentBarcodeHistory;
use App\Models\StudentCardReport;
use Milon\Barcode\DNS1D;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Intervention\Image\ImageManagerStatic as Image;

class StudentBarcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Student::with(['user', 'classroom.school', 'pendingCardReport'])->hasSchool()
                ->when(request('school_id'), function ($query) {
                    $query->whereHas('classroom', function ($query) {
                        $query->where('school_id', request('school_id'));
                    });
                })
                ->when(request('classroom_id'), function ($query) {
                    $query->where('classroom_id', request('classroom_id'));
                })
                ->when(request('status'), function ($query) {
                    $query->where('status', request('status'));
                })
                ->when(request('name'), function ($query) {
                    $query->where('name', 'like', '%' . request('name') . '%');
                });
            return DataTables::of($data)
                ->editColumn('name', function ($data) {
                    $output = '<div><strong class="text-gray-800">' . e($data->name) . '</strong></div>';
                    if ($data->pendingCardReport) {
                        $output .= '<div class="mt-1"><span class="badge badge-light-danger fw-bolder px-2 py-1 fs-8" title="' . e($data->pendingCardReport->notes ?? '') . '"><i class="fa fa-exclamation-triangle text-danger me-1"></i>' . e($data->pendingCardReport->issue_label) . '</span></div>';
                    }
                    return $output;
                })
                ->addColumn('classroom', function ($data) {
                    return $data->classroom->name ?? 'Belum ada kelas';
                })
                ->addColumn('school', function ($data) {
                    return $data->classroom->school->name ?? 'Belum ada sekolah';
                })
                ->editColumn('barcode', function ($data) {
                    $code = $data->barcode ? e($data->barcode) : '<span class="text-muted fst-italic">Belum ada</span>';
                    $prev = $data->previous_barcode ? '<br><small class="text-muted fs-8" title="Barcode sebelumnya"><i class="fa fa-history fa-xs me-1"></i>Prev: ' . e($data->previous_barcode) . '</small>' : '';
                    return '<div><span class="font-monospace fw-bold text-gray-700">' . $code . '</span>' . $prev . '</div>';
                })
                ->addColumn('action', function ($data) {
                    $safeName = e($data->name);
                    $safeNis = e($data->nis ?? '-');
                    $safeBarcode = e($data->barcode ?? '');
                    $safePrevBarcode = e($data->previous_barcode ?? '');
                    $actionEditUrl = route('student-barcode.change-barcode', $data->id);
                    $actionDownloadUrl = route('student-barcode.download-png', $data->id);

                    // 1. Edit Barcode Button
                    $btnEdit = "<button type='button' class='btn btn-primary btn-sm me-1 px-3 py-2 btn-edit-barcode' " .
                        "data-id='{$data->id}' " .
                        "data-name='{$safeName}' " .
                        "data-nis='{$safeNis}' " .
                        "data-barcode='{$safeBarcode}' " .
                        "data-previous='{$safePrevBarcode}' " .
                        "title='Edit Barcode Santri'>" .
                        "<i class='fas fa-edit me-1'></i> Edit</button>";

                    // 2. Rollback Button
                    if (!empty($data->previous_barcode)) {
                        $btnRollback = "<button type='button' class='btn btn-warning btn-sm me-1 px-3 py-2 btn-rollback-barcode' " .
                            "data-id='{$data->id}' " .
                            "data-name='{$safeName}' " .
                            "data-previous='{$safePrevBarcode}' " .
                            "title='Kembalikan ke barcode sebelumnya: {$safePrevBarcode}'>" .
                            "<i class='fas fa-undo me-1'></i> Rollback</button>";
                    } else {
                        $btnRollback = "<button type='button' class='btn btn-light-secondary text-muted btn-sm me-1 px-3 py-2' " .
                            "disabled title='Belum ada riwayat barcode sebelumnya'>" .
                            "<i class='fas fa-undo me-1'></i> Rollback</button>";
                    }

                    // 3. Quick Generate Button & Download Button
                    $btnGenerate = "<a href='{$actionEditUrl}' class='btn btn-icon btn-light-info btn-sm me-1 w-30px h-30px' " .
                        "onclick=\"return confirm('Apakah Anda yakin ingin generate acak barcode baru untuk santri {$safeName}? Barcode lama akan disimpan dan dapat di-rollback.');\" " .
                        "title='Generate Acak Baru & Unduh PNG'>" .
                        "<i class='fas fa-sync-alt fa-xs'></i></a>";

                    $btnDownload = "<a href='{$actionDownloadUrl}' class='btn btn-icon btn-light-success btn-sm w-30px h-30px' " .
                        "title='Unduh Gambar Barcode PNG'>" .
                        "<i class='fas fa-download fa-xs'></i></a>";

                    return "<div class='d-flex align-items-center justify-content-center flex-nowrap'>" .
                        $btnEdit . $btnRollback . $btnGenerate . $btnDownload .
                        "</div>";
                })
                ->rawColumns(['name', 'action', 'classroom', 'school', 'barcode'])
                ->make(true);
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        $pendingReportsCount = StudentCardReport::pending()->count();
        return view('admins.student-barcode.index', compact('schools', 'pendingReportsCount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $schools = School::orderBy('name')->hasSchool()->get();
        return view('admins.student-barcode.create', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'student_ids' => 'required|exists:students,id',
        ]);

        // Get the list of students based on the provided IDs
        $users = Student::whereIn('id', $request->student_ids)->get();

        // Create a temporary folder to store the barcode images
        $tempFolder = storage_path('app/temp_barcodes/');
        if (!file_exists($tempFolder)) {
            mkdir($tempFolder, 0777, true);
        }

        // Array to hold the paths of the generated barcode images
        $barcodeImages = [];

        // Generate barcode images for each user and resize them
        foreach ($users as $user) {
            $dns1d = new DNS1D;

            // Generate the barcode in base64 PNG format with higher resolution
            $barcodeImage = $dns1d->getBarcodePNG($user['barcode'], 'C128', 3, 100); // Width and height scaling
            $imageData = base64_decode($barcodeImage);

            // Load the barcode image using Intervention Image
            $image = Image::make($imageData);

            // Resize the image to the desired dimensions in cm
            // Convert dimensions from cm to pixels
            $widthInPixels = 6.5 * 37.8; // 6.5 cm to pixels (now width)
            $heightInPixels = 0.9 * 37.8; // 0.9 cm to pixels (now height)

            // Resize the image without losing quality
            $image->resize($widthInPixels, $heightInPixels, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize(); // Prevent upsizing
            });

            // Create a custom filename for each barcode image
            $fileName = $user['nis'] ? $user['nis'] . '.png' : $user['name'] . '.png';
            $filePath = $tempFolder . $fileName;

            // Save the resized image to the temporary folder in PNG format first
            $image->save($filePath, 100, 'png'); // Save as PNG for better quality

            // Add the image path to the array for zipping
            $barcodeImages[] = $filePath;
        }

        // Zip all the barcode images
        $zipFileName = 'barcodes.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
            // Add each barcode image to the zip file
            foreach ($barcodeImages as $image) {
                $zip->addFile($image, basename($image));
            }

            // Close the zip file
            $zip->close();
        }

        // Clean up the temporary barcode images
        foreach ($barcodeImages as $image) {
            if (file_exists($image)) {
                unlink($image);
            }
        }

        // Delete the temp folder after use
        if (is_dir($tempFolder)) {
            // Remove any remaining files
            Storage::deleteDirectory('temp_barcodes');
        }

        // Download the zip file and then delete it after sending
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }


    // public function store(Request $request)
    // {
    //     // Validate the incoming request
    //     $request->validate([
    //         'student_ids' => 'required|exists:students,id',
    //     ]);

    //     // Get the list of students based on the provided IDs
    //     $users = Student::whereIn('id', $request->student_ids)->get();

    //     // Create a temporary folder to store the barcode images
    //     $tempFolder = storage_path('app/temp_barcodes/');
    //     if (!file_exists($tempFolder)) {
    //         mkdir($tempFolder, 0777, true);
    //     }

    //     // Array to hold the paths of the generated barcode images
    //     $barcodeImages = [];

    //     // Generate barcode images for each user and resize them
    //     foreach ($users as $user) {
    //         $dns1d = new DNS1D;

    //         // Generate the barcode in base64 PNG format
    //         $barcodeImage = $dns1d->getBarcodePNG($user['barcode'], 'C128');
    //         $imageData = base64_decode($barcodeImage);

    //         // Load the barcode image using Intervention Image
    //         $image = Image::make($imageData);

    //         // Resize the image to the desired dimensions in cm
    //         // Convert dimensions from cm to pixels
    //         $widthInPixels = 6.5 * 37.8; // 6.5 cm to pixels (now width)
    //         $heightInPixels = 0.9 * 37.8; // 0.9 cm to pixels (now height)

    //         // Resize the image
    //         $image->resize($widthInPixels, $heightInPixels);

    //         // Create a custom filename for each barcode image
    //         $fileName = $user['nis'] ? $user['nis'] . '.png' : $user['name'] . '.png';
    //         $filePath = $tempFolder . $fileName;

    //         // Save the resized image to the temporary folder
    //         $image->save($filePath);

    //         // Add the image path to the array for zipping
    //         $barcodeImages[] = $filePath;
    //     }

    //     // Zip all the barcode images
    //     $zipFileName = 'barcodes.zip';
    //     $zipFilePath = storage_path('app/' . $zipFileName);

    //     $zip = new ZipArchive;
    //     if ($zip->open($zipFilePath, ZipArchive::CREATE) === TRUE) {
    //         // Add each barcode image to the zip file
    //         foreach ($barcodeImages as $image) {
    //             $zip->addFile($image, basename($image));
    //         }

    //         // Close the zip file
    //         $zip->close();
    //     }

    //     // Clean up the temporary barcode images
    //     foreach ($barcodeImages as $image) {
    //         if (file_exists($image)) {
    //             unlink($image);
    //         }
    //     }

    //     // Delete the temp folder after use
    //     if (is_dir($tempFolder)) {
    //         // Remove any remaining files
    //         Storage::deleteDirectory('temp_barcodes');
    //     }

    //     // Download the zip file and then delete it after sending
    //     return response()->download($zipFilePath)->deleteFileAfterSend(true);
    // }



    public function changeBarcode($id)
    {
        $student = Student::findOrFail($id);

        $oldBarcode = $student->barcode;
        $newBarcode = Student::generateUniqueBarcode();

        $student->update([
            'barcode' => $newBarcode,
            'previous_barcode' => $oldBarcode,
        ]);

        StudentBarcodeHistory::create([
            'student_id' => $student->id,
            'old_barcode' => $oldBarcode,
            'new_barcode' => $newBarcode,
            'action_type' => 'generated',
            'admin_id' => auth()->id(),
        ]);

        return $this->downloadBarcodePng($student->id);
    }

    /**
     * Update barcode manually from modal with uniqueness check.
     */
    public function updateBarcode(Request $request, $id)
    {
        $request->validate([
            'barcode' => [
                'required',
                'string',
                'min:5',
                'max:64',
                \Illuminate\Validation\Rule::unique('students', 'barcode')->ignore($id),
            ],
        ], [
            'barcode.required' => 'Barcode wajib diisi.',
            'barcode.unique' => 'Barcode sudah digunakan oleh santri lain.',
            'barcode.min' => 'Barcode minimal 5 karakter.',
            'barcode.max' => 'Barcode maksimal 64 karakter.',
        ]);

        $student = Student::findOrFail($id);
        $newBarcode = trim($request->input('barcode'));
        $oldBarcode = $student->barcode;

        if ($oldBarcode !== $newBarcode) {
            $student->update([
                'barcode' => $newBarcode,
                'previous_barcode' => $oldBarcode,
            ]);

            StudentBarcodeHistory::create([
                'student_id' => $student->id,
                'old_barcode' => $oldBarcode,
                'new_barcode' => $newBarcode,
                'action_type' => 'manual_edit',
                'admin_id' => auth()->id(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => "Barcode santri {$student->name} berhasil diperbarui.",
            'barcode' => $newBarcode,
            'previous_barcode' => $student->previous_barcode,
            'download_url' => route('student-barcode.download-png', $student->id),
        ]);
    }

    /**
     * Rollback barcode to previous state.
     */
    public function rollbackBarcode(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        if (empty($student->previous_barcode)) {
            return response()->json([
                'success' => false,
                'message' => 'Santri ini belum memiliki riwayat barcode sebelumnya untuk di-rollback.',
            ], 422);
        }

        $targetBarcode = $student->previous_barcode;

        // Collision check
        $conflict = Student::where('barcode', $targetBarcode)
            ->where('id', '!=', $student->id)
            ->first();

        if ($conflict) {
            return response()->json([
                'success' => false,
                'message' => "Gagal rollback: Barcode lama ({$targetBarcode}) saat ini sedang digunakan oleh santri lain: {$conflict->name}.",
            ], 422);
        }

        $currentBarcode = $student->barcode;

        $student->update([
            'barcode' => $targetBarcode,
            'previous_barcode' => $currentBarcode, // One step rollback allows toggle back if needed
        ]);

        StudentBarcodeHistory::create([
            'student_id' => $student->id,
            'old_barcode' => $currentBarcode,
            'new_barcode' => $targetBarcode,
            'action_type' => 'rollback',
            'admin_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Barcode santri {$student->name} berhasil dikembalikan ke state sebelumnya ({$targetBarcode}).",
            'barcode' => $targetBarcode,
            'previous_barcode' => $student->previous_barcode,
        ]);
    }

    /**
     * Generate unique 17-digit random barcode via API.
     */
    public function generateUnique()
    {
        return response()->json([
            'success' => true,
            'barcode' => Student::generateUniqueBarcode(),
        ]);
    }

    /**
     * Download barcode PNG for student in HD quality (Best Resolution).
     */
    public function downloadBarcodePng($id)
    {
        $student = Student::findOrFail($id);

        if (empty($student->barcode)) {
            return redirect()->back()->with('error', 'Santri belum memiliki barcode.');
        }

        $dns1d = new DNS1D;
        // Native high-resolution C128 barcode (w=12px per module, h=220px ~ 630 DPI print quality, zero interpolation blur)
        $barcodeImage = $dns1d->getBarcodePNG($student->barcode, 'C128', 12, 220);
        $imageData = base64_decode($barcodeImage);

        $rawName = $student->nis ?: $student->name;
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $rawName) . '.png';

        return response($imageData, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
