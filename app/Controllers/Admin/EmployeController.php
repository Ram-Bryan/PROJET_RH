<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class EmployeController extends BaseController
{
    public function index(): string
    {
        $db = db_connect();

        $employes = $db->table('v_employes_detail')
            ->orderBy('nom', 'ASC')
            ->orderBy('prenom', 'ASC')
            ->get()
            ->getResultArray();

        $departements = $db->table('departements')
            ->orderBy('nom', 'ASC')
            ->get()
            ->getResultArray();

        return view('admin/employes', [
            'employes' => $employes,
            'departements' => $departements,
        ]);
    }
}
