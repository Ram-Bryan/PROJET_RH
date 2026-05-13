<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('rh/dashboard');
    }
}
