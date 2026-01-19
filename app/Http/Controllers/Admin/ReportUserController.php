<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportUserController extends Controller
{
    public function index()
    {
        return view('admins.report-user.index');
    }

    public function export(Request $request)
    {
        $data = [
            'from' => $request->from,
            'to' => $request->to,
        ];

        return view('admins.report-user.export', $data);
    }
}
