<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Help;
use Illuminate\Http\Request;
use App\Models\AppInformation;
use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;

class InformationController extends Controller
{

    public function index()
    {
        $information = AppInformation::latest()->first();
        return $this->getSuccessResponse($information);
    }

    public function help()
    {
        $helps = Help::latest()->get();
        return $this->getSuccessResponse($helps);
    }

    public function appSetting()
    {
        $applicationSetting = ApplicationSetting::latest()->first();
        return $this->getSuccessResponse($applicationSetting);
    }
}
