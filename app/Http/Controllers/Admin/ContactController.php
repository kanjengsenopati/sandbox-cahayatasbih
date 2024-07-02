<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contact;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactRequest;

class ContactController extends Controller
{
    public function __construct()
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $data = Contact::latest()->get();
            return DataTables::of($data)
                ->editColumn('type', function ($data) {
                    if ($data->type == Contact::TYPE_SUPERADMIN) {
                        return '<span class="badge badge-primary">SuperAdmin</span>';
                    } elseif ($data->type == Contact::TYPE_BENDAHARA) {
                        return '<span class="badge badge-success">Bendahara</span>';
                    }
                })
                ->addColumn('action', function ($data) {
                    $actionEdit = route('contact.edit', $data->id);
                    $actionDelete = route('contact.destroy', $data->id);
                    return "<div class='d-flex justify-content-center'>" .
                        view('components.action.edit', ['action' => $actionEdit]) .
                        view('components.action.delete', ['action' => $actionDelete, 'id' => $data->id]) .
                        "</div>";
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }
        return view('admins.contact.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contact = new Contact();
        $types = $contact->getListType();
        return view('admins.contact.create-edit', compact('types'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactRequest $request)
    {
        Contact::create($request->validated());
        return redirect()->route('contact.index')->with('success', 'Kontak berhasil ditambahkan');
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
    public function edit(Contact $contact)
    {
        $types = $contact->getListType();
        return view('admins.contact.create-edit', compact('contact', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactRequest $request, Contact $contact)
    {
        $contact->update($request->validated());
        return redirect()->route('contact.index')->with('success', 'Kontak berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('contact.index')->with('success', 'Kontak berhasil dihapus');
    }
}
