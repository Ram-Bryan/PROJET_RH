<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index(): string
    {
        return view('employe/dashboard');
    }
}
