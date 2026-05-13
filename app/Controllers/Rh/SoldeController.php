<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\DepartementModel;
use App\Models\EmployeModel;
use App\Models\SoldeModel;

class SoldeController extends BaseController
{
    public function index()
    {
        $rhId = (int) session('user_id');
        if ($rhId <= 0) {
            return redirect()->to('/');
        }

        $annee = (int) ($this->request->getGet('annee') ?? date('Y'));
        $departementId = $this->request->getGet('departement_id');
        $departementId = $departementId !== null && $departementId !== '' ? (int) $departementId : null;
        $employeId = $this->request->getGet('employe_id');
        $employeId = $employeId !== null && $employeId !== '' ? (int) $employeId : null;

        $soldeModel = new SoldeModel();
        $employeModel = new EmployeModel();
        $deptModel = new DepartementModel();

        $rows = $soldeModel->getDetailForRh($annee, $departementId, $employeId);

        $grouped = [];
        foreach ($rows as $row) {
            $eid = (int) $row['employe_id'];
            if (!isset($grouped[$eid])) {
                $prenom = (string) ($row['employe_prenom'] ?? '');
                $nom = (string) ($row['employe_nom'] ?? '');
                $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
                $initials = $initials !== '' ? $initials : '??';

                $grouped[$eid] = [
                    'employe_id' => $eid,
                    'employe' => trim($prenom . ' ' . $nom),
                    'initials' => $initials,
                    'departement' => (string) ($row['departement_nom'] ?? ''),
                    'soldes' => [],
                ];
            }

            $attribues = (int) $row['jours_attribues'];
            $restant = (int) $row['jours_restant'];
            $pris = (int) $row['jours_pris'];
            $percent = $attribues > 0 ? (int) round(($restant / $attribues) * 100) : 0;
            $class = $percent <= 25 ? 'danger' : ($percent <= 50 ? 'warn' : '');

            $grouped[$eid]['soldes'][] = [
                'type' => (string) $row['type_conge_libelle'],
                'attribues' => $attribues,
                'pris' => $pris,
                'restant' => $restant,
                'percent' => $percent,
                'class' => $class,
            ];
        }

        $departements = $deptModel->orderBy('nom', 'ASC')->findAll();
        $employes = $employeModel->getActifs($departementId);

        $rhDetail = $employeModel->getDetail($rhId) ?? [];
        $prenom = (string) ($rhDetail['prenom'] ?? session('prenom') ?? '');
        $nom = (string) ($rhDetail['nom'] ?? session('nom') ?? '');
        $initials = strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        $initials = $initials !== '' ? $initials : '??';

        return view('rh/soldes', [
            'rh' => ['prenom' => $prenom, 'nom' => $nom, 'initials' => $initials],
            'annee' => $annee,
            'departementIdActif' => $departementId,
            'employeIdActif' => $employeId,
            'departements' => $departements,
            'employes' => $employes,
            'employesSoldes' => array_values($grouped),
        ]);
    }
}

