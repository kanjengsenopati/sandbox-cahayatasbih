<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Models\ApplicationMenu;
use App\Http\Controllers\Controller;

class ApplicationMenuController extends Controller
{
    public function index()
    {
        $menu = ApplicationMenu::orderBy('order', 'asc')->orderBy('created_at', 'asc')->get();
        return $this->getSuccessResponse($menu);
    }
}
