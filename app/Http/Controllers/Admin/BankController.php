<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\BankRequest;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!Auth::user()->can('Manage Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        if (request()->ajax()) {
            $data = Bank::latest()->get();
            return DataTables::of($data)
                ->addColumn('status', function ($data) {
                    return $data->is_active ? '<span class="badge badge-success">Aktif</span>'
                        : '<span class="badge badge-danger">Tidak Aktif</span>';
                })
                ->editColumn('image', function ($data) {
                    return "<img src='" . asset($data->image) . "' class='img-fluid' style='max-width: 100px'>";
                })
                ->addColumn('action', function ($data) {
                    $actionStatus = route('application-menu.status', $data->id);
                    $actionEdit = route('bank.edit', $data->id);
                    $actionDelete = route('bank.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.status', ['action' => $actionStatus, 'status' => $data->is_active, 'id' => $data->id]) .
                        view('components.action.edit', ['action' => $actionEdit, 'name' => 'Bank']) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id, 'name' => 'Bank']) .
                        "</div>";
                })
                ->rawColumns(['action', 'status', 'image'])
                ->make(true);
        }
        return view('admins.bank.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!Auth::user()->can('Create Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.bank.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankRequest $request)
    {
        if (!Auth::user()->can('Create Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = 'storage/' . $request->file('image')->store('images/bank', 'public');
        }
        Bank::create($data);
        return redirect()->route('bank.index')->with('success', 'Bank berhasil ditambahkan');
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
    public function edit(Bank $bank)
    {
        if (!Auth::user()->can('Edit Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        return view('admins.bank.create-edit', compact('bank'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankRequest $request, Bank $bank)
    {
        if (!Auth::user()->can('Edit Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $data = $request->validated();
        if ($request->hasFile('image')) {
            file_exists($bank->image) ? unlink($bank->image) : null;
            $data['image'] = 'storage/' . $request->file('image')->store('images/bank', 'public');
        }
        $bank->update($data);
        return redirect()->route('bank.index')->with('success', 'Bank berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        if (!Auth::user()->can('Delete Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        file_exists($bank->image) ? unlink($bank->image) : null;
        $bank->delete();
        return redirect()->route('bank.index')->with('success', 'Bank berhasil dihapus');
    }

    /**
     * Change the specified resource status.
     */

    public function status(string $id)
    {
        if (!Auth::user()->can('Edit Bank')) {
            return redirect()->back()->with('error', 'Maaf, Anda tidak memiliki akses untuk halaman tersebut');
        }
        $bank = Bank::findOrFail($id);
        $bank->update(['is_active' => !$bank->is_active]);
        return redirect()->route('bank.index')->with('success', 'Status bank berhasil diperbarui');
    }
}
