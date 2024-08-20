<?php

namespace App\Http\Controllers\Admin;

use ZipArchive;
use App\Models\School;
use App\Models\Student;
use Milon\Barcode\DNS1D;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
            $data = Student::with('user', 'classroom.school')->hasSchool()
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
                ->latest();
            return DataTables::of($data)
                ->addColumn('classroom', function ($data) {
                    return $data->classroom->name ?? 'Belum ada kelas';
                })
                ->addColumn('school', function ($data) {
                    return $data->classroom->school->name ?? 'Belum ada sekolah';
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('student-barcode.change-barcode', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        "<a href='$actionEdit' class='btn btn-primary btn-sm mr-1' " .
                        "onclick=\"return confirm('Apakah Anda yakin ingin mengganti barcode? Barcode yang lama tidak akan berfungsi.');\">" .
                        "<i class='fas fa-edit fa-sm'></i> Ganti Barcode</a>" .
                        "</div>";
                })
                ->rawColumns(['action', 'classroom', 'school'])
                ->make(true);
        }
        $schools = School::hasSchool()->orderBy('name')->get();
        return view('admins.student-barcode.index', compact('schools'));
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

            // Generate the barcode in base64 PNG format
            $barcodeImage = $dns1d->getBarcodePNG($user['barcode'], 'C128');
            $imageData = base64_decode($barcodeImage);

            // Load the barcode image using Intervention Image
            $image = Image::make($imageData);

            // Resize the image to the desired dimensions in cm
            // Convert dimensions from cm to pixels
            $widthInPixels = 6.5 * 37.8; // 6.5 cm to pixels (now width)
            $heightInPixels = 0.9 * 37.8; // 0.9 cm to pixels (now height)

            // Resize the image
            $image->resize($widthInPixels, $heightInPixels);

            // Create a custom filename for each barcode image
            $fileName = $user['nis'] ? $user['nis'] . '.png' : $user['name'] . '.png';
            $filePath = $tempFolder . $fileName;

            // Save the resized image to the temporary folder
            $image->save($filePath);

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
            unlink($image);
        }

        // Delete the temp folder after use
        rmdir($tempFolder);

        // Download the zip file and then delete it after sending
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }



    public function changeBarcode($id)
    {
        $student = Student::findOrFail($id);

        // Generate new barcode
        $barcode = $this->generateBarcode();
        $student->update(['barcode' => $barcode]);

        // Generate barcode image
        $dns1d = new DNS1D;
        $barcodeImage = $dns1d->getBarcodePNG($barcode, 'C128');
        $imageData = base64_decode($barcodeImage);

        // Create image and resize it to the specified dimensions
        $image = Image::make($imageData);

        // Convert dimensions from cm to pixels
        $widthInPixels = 6.5 * 37.8; // 6.5 cm to pixels
        $heightInPixels = 0.9 * 37.8; // 0.9 cm to pixels

        // Resize the image
        $image->resize($widthInPixels, $heightInPixels);

        // Create a custom filename for the barcode image
        $fileName = $student->nis . '.png';
        $tempPath = storage_path('app/public/') . $fileName;

        // Save the resized image temporarily
        $image->save($tempPath);

        // Download the barcode image and delete the file after sending
        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    private function generateBarcode()
    {
        return substr(str_shuffle(str_repeat('0123456789', 17)), 0, 17);
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
