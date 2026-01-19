<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppInformation;
use Illuminate\Http\Request;

class PrivacyPolicyController extends Controller
{
    public function index()
    {
        $data = AppInformation::latest()->first();
        return view('admins.app-information.privacy-policy.index', compact('data'));
    }

    public function termCondition()
    {
        $data = AppInformation::latest()->first();
        return view('admins.app-information.term-and-condition.index', compact('data'));
    }

    public function aboutUs()
    {
        $data = AppInformation::latest()->first();
        return view('admins.app-information.about-us.index', compact('data'));
    }
}
