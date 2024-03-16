<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\ApplicationMenu;
use App\Http\Controllers\Controller;

class ApplicationMenuController extends Controller
{
    public function index()
    {
        $menu = ApplicationMenu::latest()->get();
        return $this->getSuccessResponse($menu);
    }
}
