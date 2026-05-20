<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CongeModel;
use App\Models\DepartementModel;
use App\Models\EmployeModel;

class DashboardController extends BaseController
{
    public function index(): string
    {
        $employeModel = new EmployeModel();
        $congeModel = new CongeModel();
        $departementModel = new DepartementModel();
        $annee = (int) date('Y');

        return view('admin/dashboard', [
            'employesActifs' => $employeModel->countActifs(),
            'demandesEnAttente' => $congeModel->countPending(),
            'approuveesCeMois' => $congeModel->countApprovedForMonth(date('Y-m')),
            'departementsCount' => $departementModel->countAllResults(),
            'recentes' => $congeModel->getRecentDetails(6),
            'anneeGraph' => $annee,
            'congesParMois' => $congeModel->getMonthlyCounts($annee),
            'congesParJour' => $congeModel->getWeekdayCounts($annee),
        ]);
    }
}
