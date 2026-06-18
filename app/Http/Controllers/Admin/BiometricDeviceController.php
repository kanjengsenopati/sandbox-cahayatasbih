<?php

namespace App\Http\Controllers\Admin;

use App\Models\BiometricDevice;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BiometricDeviceController extends Controller
{
    public function index()
    {
        if (!Auth::user()->can('Manage Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        if (request()->ajax()) {
            $data = BiometricDevice::latest()->get();
            return DataTables::of($data)
                ->addColumn('status', function ($row) {
                    return $row->is_active ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->addColumn('action', function ($row) {
                    $actionStatus = route('biometric-device.status', $row->id);
                    $actionEdit = route('biometric-device.edit', $row->id);
                    $actionDelete = route('biometric-device.destroy', $row->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $row->is_active, 'id' => $row->id, 'name' => 'Biometric']) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Biometric']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $row->id, 'name' => 'Biometric']) .
                        "</div>";
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('admins.biometric-device.index');
    }

    public function create()
    {
        if (!Auth::user()->can('Create Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.biometric-device.create-edit');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('Create Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'device_name' => 'required|string|max:255',
            'device_ip' => 'nullable|string|max:45',
            'location' => 'required|string|max:255',
        ]);

        BiometricDevice::create([
            'device_name' => $request->device_name,
            'device_ip' => $request->device_ip,
            'location' => $request->location,
            'auth_token' => 'BT-' . Str::upper(Str::random(24)),
            'is_active' => true,
        ]);

        return redirect()->route('biometric-device.index')->with('success', 'Perangkat biometrik berhasil ditambahkan');
    }

    public function edit(BiometricDevice $biometricDevice)
    {
        if (!Auth::user()->can('Edit Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.biometric-device.create-edit', compact('biometricDevice'));
    }

    public function update(Request $request, BiometricDevice $biometricDevice)
    {
        if (!Auth::user()->can('Edit Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }

        $request->validate([
            'device_name' => 'required|string|max:255',
            'device_ip' => 'nullable|string|max:45',
            'location' => 'required|string|max:255',
        ]);

        $biometricDevice->update([
            'device_name' => $request->device_name,
            'device_ip' => $request->device_ip,
            'location' => $request->location,
        ]);

        return redirect()->route('biometric-device.index')->with('success', 'Perangkat biometrik berhasil diperbarui');
    }

    public function destroy(BiometricDevice $biometricDevice)
    {
        if (!Auth::user()->can('Delete Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $biometricDevice->delete();
        return redirect()->route('biometric-device.index')->with('success', 'Perangkat biometrik berhasil dihapus');
    }

    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Biometric')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $device = BiometricDevice::findOrFail($id);
        $device->update(['is_active' => !$device->is_active]);
        return redirect()->route('biometric-device.index')->with('success', 'Status perangkat biometrik berhasil diperbarui');
    }
}
